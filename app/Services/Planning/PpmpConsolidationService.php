<?php

namespace App\Services\Planning;

use App\Enums\PpmpConsolidationStatus;
use App\Enums\PpmpConsolidationStep;
use App\Enums\PpmpDocumentType;
use App\Enums\PpmpStatus;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Planning\PpmpConsolidationBp2020Line;
use App\Models\Planning\PpmpConsolidationItem;
use App\Models\Planning\PpmpConsolidationVersion;
use App\Models\Planning\PpmpConsolidationWfpLine;
use App\Models\Planning\PpmpItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PpmpConsolidationService
{
    /**
     * @param  array<int, string>  $ppmpIds
     */
    public function createDraft(
        User $user,
        string $fiscalYearId,
        PpmpDocumentType $documentType,
        array $ppmpIds,
        ?string $title = null,
    ): PpmpConsolidation {
        $this->assertSelectablePpmps($ppmpIds, $documentType, $fiscalYearId);

        return DB::transaction(function () use ($user, $fiscalYearId, $documentType, $ppmpIds, $title) {
            $consolidation = PpmpConsolidation::query()->create([
                'fiscal_year_id' => $fiscalYearId,
                'reference_no' => $this->nextReferenceNo($fiscalYearId),
                'title' => $title ?: $this->defaultTitle($documentType, $fiscalYearId),
                'document_type' => $documentType,
                'status' => PpmpConsolidationStatus::Draft,
                'current_step' => PpmpConsolidationStep::SelectPpmps->value,
                'created_by' => $user->id,
            ]);

            $this->syncSources($consolidation, $ppmpIds);

            return $consolidation->fresh(['sourcePpmps.division', 'fiscalYear']);
        });
    }

    /**
     * @param  array<int, string>  $ppmpIds
     */
    public function syncSources(PpmpConsolidation $consolidation, array $ppmpIds): void
    {
        $this->assertSelectablePpmps(
            $ppmpIds,
            $consolidation->document_type,
            $consolidation->fiscal_year_id,
            $consolidation->id,
        );

        $consolidation->sources()->whereNotIn('ppmp_id', $ppmpIds)->delete();

        foreach ($ppmpIds as $ppmpId) {
            $consolidation->sources()->firstOrCreate(['ppmp_id' => $ppmpId]);
        }
    }

    public function generateConsolidatedItems(PpmpConsolidation $consolidation, bool $mergeDuplicates = true): PpmpConsolidation
    {
        return DB::transaction(function () use ($consolidation, $mergeDuplicates) {
            $consolidation->items()->delete();

            $sourcePpmps = $consolidation->sourcePpmps()
                ->with(['items.modeOfProcurement', 'items.fundSource', 'items.pap', 'items.uacsCode', 'division'])
                ->get();

            $rawItems = $sourcePpmps->flatMap(function (Ppmp $ppmp) {
                return $ppmp->items->map(fn (PpmpItem $item) => [
                    'ppmp' => $ppmp,
                    'item' => $item,
                ]);
            });

            $groups = $mergeDuplicates
                ? $this->groupItemsForMerge($rawItems)
                : $rawItems->map(fn (array $row) => collect([$row]));

            $itemNo = 1;

            foreach ($groups as $groupKey => $group) {
                /** @var Collection<int, array{ppmp: Ppmp, item: PpmpItem}> $group */
                $first = $group->first();
                $ppmp = $first['ppmp'];
                $item = $first['item'];

                $quantity = $group->sum(fn (array $row) => (float) $row['item']->quantity);
                $unitCost = $group->count() === 1
                    ? (float) $item->estimated_unit_cost
                    : $this->weightedUnitCost($group);
                $lineAbc = round($quantity * $unitCost, 2);

                PpmpConsolidationItem::query()->create([
                    'ppmp_consolidation_id' => $consolidation->id,
                    'source_ppmp_id' => $ppmp->id,
                    'division_id' => $ppmp->division_id,
                    'group_key' => (string) $groupKey,
                    'source_ppmp_item_ids' => $group->pluck('item.id')->values()->all(),
                    'item_no' => $itemNo++,
                    'expense_class' => $item->expense_class?->value,
                    'project_type' => $item->project_type?->value,
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'specification' => $item->specification,
                    'unit' => $item->unit,
                    'quantity' => $quantity,
                    'estimated_unit_cost' => $unitCost,
                    'line_abc' => $lineAbc,
                    'mode_of_procurement_id' => $item->mode_of_procurement_id,
                    'fund_source_id' => $item->fund_source_id,
                    'pap_id' => $item->pap_id,
                    'uacs_code_id' => $item->uacs_code_id,
                    'schedule_start' => $item->schedule_start,
                    'schedule_end' => $item->schedule_end,
                    'pre_procurement_conference' => $item->pre_procurement_conference?->value,
                    'remarks' => $item->remarks,
                    'is_merged' => $group->count() > 1,
                    'sort_order' => $itemNo,
                ]);
            }

            $consolidation->recalculateTotal();
            $consolidation->update(['current_step' => PpmpConsolidationStep::GenerateConsolidated->value]);

            return $consolidation->fresh(['items.division', 'items.modeOfProcurement', 'items.fundSource']);
        });
    }

    public function generateBp2020(PpmpConsolidation $consolidation): PpmpConsolidation
    {
        return DB::transaction(function () use ($consolidation) {
            $consolidation->bp2020Lines()->delete();

            $consolidation->load(['items.pap', 'items.fundSource', 'items.division']);

            foreach ($consolidation->items as $index => $item) {
                PpmpConsolidationBp2020Line::query()->create([
                    'ppmp_consolidation_id' => $consolidation->id,
                    'ppmp_consolidation_item_id' => $item->id,
                    'program' => $item->pap?->name,
                    'activity' => $item->division?->name,
                    'project' => $item->item_name,
                    'procurement_item' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_cost' => $item->estimated_unit_cost,
                    'annual_requirement' => $item->lineAbc(),
                    'budget_allocation' => $item->lineAbc(),
                    'fund_source' => $item->fundSource?->name,
                    'sort_order' => $index + 1,
                ]);
            }

            $consolidation->update(['current_step' => PpmpConsolidationStep::GenerateBp2020->value]);

            return $consolidation->fresh('bp2020Lines');
        });
    }

    public function generateWfp(PpmpConsolidation $consolidation): PpmpConsolidation
    {
        return DB::transaction(function () use ($consolidation) {
            $consolidation->wfpLines()->delete();
            $consolidation->load(['items.division', 'items.fundSource']);

            foreach ($consolidation->items as $index => $item) {
                $budget = $item->lineAbc();
                $quarter = round($budget / 4, 2);
                $remainder = round($budget - ($quarter * 4), 2);

                PpmpConsolidationWfpLine::query()->create([
                    'ppmp_consolidation_id' => $consolidation->id,
                    'activity' => $item->item_name,
                    'responsible_office' => $item->division?->name,
                    'expected_output' => Str::limit(strip_tags((string) $item->description), 255),
                    'funding_source' => $item->fundSource?->name,
                    'budget_allocation' => $budget,
                    'q1_budget' => $quarter + $remainder,
                    'q2_budget' => $quarter,
                    'q3_budget' => $quarter,
                    'q4_budget' => $quarter,
                    'schedule_start' => $item->schedule_start,
                    'schedule_end' => $item->schedule_end,
                    'sort_order' => $index + 1,
                ]);
            }

            $consolidation->update(['current_step' => PpmpConsolidationStep::GenerateWfp->value]);

            return $consolidation->fresh('wfpLines');
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function validate(PpmpConsolidation $consolidation): array
    {
        $consolidation->load(['items.modeOfProcurement', 'items.fundSource', 'items.uacsCode', 'bp2020Lines', 'wfpLines']);
        $issues = [];

        $seen = [];
        foreach ($consolidation->items as $item) {
            $key = Str::lower(trim($item->item_name)).'|'.Str::lower(trim((string) $item->unit));

            if (isset($seen[$key]) && ! $item->is_merged) {
                $issues[] = $this->issue('duplicate', 'Duplicate procurement item detected.', $item->id, 'items');
            }
            $seen[$key] = true;

            if (! $item->unit) {
                $issues[] = $this->issue('missing_unit', 'Missing unit of measure.', $item->id, 'items');
            }

            if (! $item->mode_of_procurement_id) {
                $issues[] = $this->issue('missing_mode', 'Missing procurement method.', $item->id, 'items');
            }

            if (! $item->fund_source_id) {
                $issues[] = $this->issue('missing_fund', 'Missing fund source.', $item->id, 'items');
            }

            if (! $item->uacs_code_id) {
                $issues[] = $this->issue('missing_uacs', 'Missing account code (UACS).', $item->id, 'items');
            }

            if ($item->schedule_start && $item->schedule_end && $item->schedule_start->gt($item->schedule_end)) {
                $issues[] = $this->issue('invalid_schedule', 'Invalid procurement schedule.', $item->id, 'items');
            }

            if ($item->lineAbc() <= 0) {
                $issues[] = $this->issue('invalid_budget', 'Line budget must be greater than zero.', $item->id, 'items');
            }
        }

        $ppmpTotal = round((float) $consolidation->items->sum(fn ($item) => $item->lineAbc()), 2);
        $bpTotal = round((float) $consolidation->bp2020Lines->sum('budget_allocation'), 2);
        $wfpTotal = round((float) $consolidation->wfpLines->sum('budget_allocation'), 2);

        if ($bpTotal > 0 && abs($ppmpTotal - $bpTotal) > 0.01) {
            $issues[] = $this->issue('budget_mismatch', 'BP Form 2020 total does not match consolidated PPMP.', null, 'bp2020');
        }

        if ($wfpTotal > 0 && abs($ppmpTotal - $wfpTotal) > 0.01) {
            $issues[] = $this->issue('budget_mismatch', 'WFP total does not match consolidated PPMP.', null, 'wfp');
        }

        $consolidation->update([
            'validation_issues' => $issues,
            'current_step' => PpmpConsolidationStep::ReviewValidation->value,
            'status' => PpmpConsolidationStatus::Validation,
        ]);

        return $issues;
    }

    public function submitForApproval(PpmpConsolidation $consolidation, User $user): PpmpConsolidation
    {
        abort_if(! empty($consolidation->validation_issues), 422, 'Resolve validation issues before submitting for approval.');

        $this->createVersionSnapshot($consolidation, $user);

        $consolidation->transitionTo(
            PpmpConsolidationStatus::PlanningReview,
            'Submitted for Planning review.',
            action: 'submitted',
        );
        $consolidation->update([
            'current_step' => PpmpConsolidationStep::ApprovalWorkflow->value,
            'planning_by' => null,
            'planning_at' => null,
        ]);

        return $consolidation->fresh();
    }

    public function planningApprove(PpmpConsolidation $consolidation, User $user, ?string $remarks = null): PpmpConsolidation
    {
        $consolidation->update(['planning_by' => $user->id, 'planning_at' => now()]);
        $consolidation->transitionTo(PpmpConsolidationStatus::BudgetReview, $remarks, action: 'planning-endorsed');

        return $consolidation->fresh();
    }

    public function budgetApprove(PpmpConsolidation $consolidation, User $user, ?string $remarks = null): PpmpConsolidation
    {
        $consolidation->update(['budget_by' => $user->id, 'budget_at' => now()]);
        $consolidation->transitionTo(PpmpConsolidationStatus::AccountingReview, $remarks, action: 'budget-endorsed');

        return $consolidation->fresh();
    }

    public function accountingApprove(PpmpConsolidation $consolidation, User $user, ?string $remarks = null): PpmpConsolidation
    {
        $consolidation->update(['accounting_by' => $user->id, 'accounting_at' => now()]);
        $consolidation->transitionTo(PpmpConsolidationStatus::BacReview, $remarks, action: 'accounting-endorsed');

        return $consolidation->fresh();
    }

    public function bacApprove(PpmpConsolidation $consolidation, User $user, ?string $remarks = null): PpmpConsolidation
    {
        $consolidation->update(['bac_by' => $user->id, 'bac_at' => now()]);
        $consolidation->transitionTo(PpmpConsolidationStatus::HopeReview, $remarks, action: 'bac-endorsed');

        return $consolidation->fresh();
    }

    public function hopeApprove(PpmpConsolidation $consolidation, User $user, ?string $remarks = null): PpmpConsolidation
    {
        $consolidation->update([
            'hope_by' => $user->id,
            'hope_at' => now(),
            'approved_by' => $user->id,
            'approved_at' => now(),
            'current_step' => PpmpConsolidationStep::FinalConsolidation->value,
        ]);
        $consolidation->transitionTo(PpmpConsolidationStatus::Approved, $remarks, action: 'hope-approved');

        return $consolidation->fresh();
    }

    public function returnForRevision(PpmpConsolidation $consolidation, User $user, string $remarks): PpmpConsolidation
    {
        $consolidation->transitionTo(PpmpConsolidationStatus::ReturnedForRevision, $remarks, action: 'returned');
        $consolidation->update(['current_step' => PpmpConsolidationStep::GenerateConsolidated->value]);

        return $consolidation->fresh();
    }

    public function lock(PpmpConsolidation $consolidation, User $user): PpmpConsolidation
    {
        $consolidation->update(['locked_by' => $user->id, 'locked_at' => now()]);
        $consolidation->transitionTo(PpmpConsolidationStatus::Locked, 'Final documents locked.', action: 'locked');

        return $consolidation->fresh();
    }

    public function cancel(PpmpConsolidation $consolidation, User $user, ?string $remarks = null): PpmpConsolidation
    {
        abort_if($consolidation->status === PpmpConsolidationStatus::Cancelled, 422, 'This consolidation has already been cancelled.');
        abort_if($consolidation->status === PpmpConsolidationStatus::Locked, 422, 'Locked consolidations cannot be cancelled.');

        $consolidation->transitionTo(
            PpmpConsolidationStatus::Cancelled,
            $remarks ?? 'Consolidation cancelled by administrator.',
            action: 'cancelled',
            enforce: false,
        );

        return $consolidation->fresh();
    }

    public function createVersionSnapshot(PpmpConsolidation $consolidation, User $user): PpmpConsolidationVersion
    {
        $consolidation->load(['items', 'bp2020Lines', 'wfpLines', 'sourcePpmps']);

        $versionNumber = ($consolidation->versions()->max('version_number') ?? 0) + 1;

        $version = $consolidation->versions()->create([
            'version_number' => $versionNumber,
            'status_at_snapshot' => $consolidation->status->value,
            'snapshot' => [
                'consolidation' => $consolidation->only(['title', 'document_type', 'total_budget', 'reference_no']),
                'items' => $consolidation->items->toArray(),
                'bp2020' => $consolidation->bp2020Lines->toArray(),
                'wfp' => $consolidation->wfpLines->toArray(),
                'sources' => $consolidation->sourcePpmps->pluck('id')->all(),
            ],
            'created_by' => $user->id,
        ]);

        $consolidation->update(['version_number' => $versionNumber]);

        return $version;
    }

    /** @return Collection<int, Ppmp> */
    public function eligiblePpmpsQuery(
        string $fiscalYearId,
        PpmpDocumentType $documentType,
        ?string $excludeConsolidationId = null,
    ) {
        $alreadyConsolidated = DB::table('ppmp_consolidation_sources')
            ->join('ppmp_consolidations', 'ppmp_consolidations.id', '=', 'ppmp_consolidation_sources.ppmp_consolidation_id')
            ->whereNull('ppmp_consolidations.deleted_at')
            ->whereNotIn('ppmp_consolidations.status', [
                PpmpConsolidationStatus::Approved->value,
                PpmpConsolidationStatus::Locked->value,
                PpmpConsolidationStatus::Cancelled->value,
            ])
            ->when($excludeConsolidationId, fn ($q) => $q->where('ppmp_consolidations.id', '!=', $excludeConsolidationId))
            ->pluck('ppmp_consolidation_sources.ppmp_id');

        return Ppmp::query()
            ->with(['division', 'fiscalYear'])
            ->withCount('items')
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('document_type', $documentType)
            ->whereIn('status', [PpmpStatus::Approved, PpmpStatus::Locked])
            ->when($alreadyConsolidated->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $alreadyConsolidated));
    }

    /** @return array<int, array{ppmp: Ppmp, consolidation: PpmpConsolidation}> */
    public function activeConsolidationWarnings(array $ppmpIds, ?string $excludeConsolidationId = null): array
    {
        $rows = DB::table('ppmp_consolidation_sources')
            ->join('ppmp_consolidations', 'ppmp_consolidations.id', '=', 'ppmp_consolidation_sources.ppmp_consolidation_id')
            ->whereIn('ppmp_consolidation_sources.ppmp_id', $ppmpIds)
            ->whereNull('ppmp_consolidations.deleted_at')
            ->whereNotIn('ppmp_consolidations.status', [
                PpmpConsolidationStatus::Approved->value,
                PpmpConsolidationStatus::Locked->value,
                PpmpConsolidationStatus::Cancelled->value,
            ])
            ->when($excludeConsolidationId, fn ($q) => $q->where('ppmp_consolidations.id', '!=', $excludeConsolidationId))
            ->get(['ppmp_consolidation_sources.ppmp_id', 'ppmp_consolidations.id as consolidation_id']);

        if ($rows->isEmpty()) {
            return [];
        }

        $consolidations = PpmpConsolidation::query()->whereIn('id', $rows->pluck('consolidation_id'))->get()->keyBy('id');
        $ppmps = Ppmp::query()->whereIn('id', $rows->pluck('ppmp_id'))->get()->keyBy('id');

        return $rows->map(fn ($row) => [
            'ppmp' => $ppmps[$row->ppmp_id],
            'consolidation' => $consolidations[$row->consolidation_id],
        ])->all();
    }

    /**
     * @param  array<int, string>  $ppmpIds
     */
    protected function assertSelectablePpmps(
        array $ppmpIds,
        PpmpDocumentType $documentType,
        string $fiscalYearId,
        ?string $excludeConsolidationId = null,
    ): void {
        abort_if($ppmpIds === [], 422, 'Select at least one PPMP.');

        $eligible = $this->eligiblePpmpsQuery($fiscalYearId, $documentType, $excludeConsolidationId)
            ->whereIn('id', $ppmpIds)
            ->pluck('id');

        abort_if($eligible->count() !== count(array_unique($ppmpIds)), 422, 'One or more selected PPMPs are not eligible for consolidation.');
    }

    protected function nextReferenceNo(string $fiscalYearId): string
    {
        $year = DB::table('fiscal_years')->where('id', $fiscalYearId)->value('year') ?? now()->year;
        $count = PpmpConsolidation::query()->where('fiscal_year_id', $fiscalYearId)->count() + 1;

        return sprintf('PC-%s-%04d', $year, $count);
    }

    protected function defaultTitle(PpmpDocumentType $documentType, string $fiscalYearId): string
    {
        $year = DB::table('fiscal_years')->where('id', $fiscalYearId)->value('year') ?? now()->year;

        return sprintf('Agency %s PPMP Consolidation FY %s', $documentType->label(), $year);
    }

    /**
     * @param  Collection<int, array{ppmp: Ppmp, item: PpmpItem}>  $rawItems
     * @return Collection<string, Collection<int, array{ppmp: Ppmp, item: PpmpItem}>>
     */
    protected function groupItemsForMerge(Collection $rawItems): Collection
    {
        return $rawItems->groupBy(function (array $row) {
            $item = $row['item'];

            return Str::lower(trim($item->item_name)).'|'.
                Str::lower(trim((string) $item->unit)).'|'.
                Str::lower(trim(strip_tags((string) $item->specification))).'|'.
                ($item->mode_of_procurement_id ?? 'none').'|'.
                ($item->fund_source_id ?? 'none');
        });
    }

    /**
     * @param  Collection<int, array{ppmp: Ppmp, item: PpmpItem}>  $group
     */
    protected function weightedUnitCost(Collection $group): float
    {
        $totalQty = $group->sum(fn (array $row) => (float) $row['item']->quantity);

        if ($totalQty <= 0) {
            return 0;
        }

        $totalCost = $group->sum(fn (array $row) => (float) $row['item']->quantity * (float) $row['item']->estimated_unit_cost);

        return round($totalCost / $totalQty, 2);
    }

    /** @return array<string, mixed> */
    protected function issue(string $type, string $message, ?string $recordId, string $section): array
    {
        return [
            'type' => $type,
            'message' => $message,
            'record_id' => $recordId,
            'section' => $section,
        ];
    }
}
