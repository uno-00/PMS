<?php

namespace App\Services\Budget;

use App\DataTransferObjects\Budget\GaaValidationResult;
use App\Enums\GaaStatus;
use App\Events\Budget\GaaApproved;
use App\Events\Budget\GaaDistributed;
use App\Imports\GaaLineItemsImport;
use App\Imports\GaaWorkbookImport;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use App\Repositories\Contracts\GaaRepositoryInterface;
use App\Services\Support\DocumentStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class GaaService
{
    public function __construct(
        protected GaaRepositoryInterface $repository,
        protected DocumentStorageService $documents,
    ) {}

    /**
     * Phase 1: Budget Officer uploads the GAA Excel template issued by DBM.
     * Creates (or replaces the draft of) the GAA record for the fiscal year
     * and parses every line item, but does not yet validate or approve it.
     */
    public function upload(FiscalYear $fiscalYear, UploadedFile $file, User $uploader, ?string $referenceNo = null): GeneralAppropriationsAct
    {
        $import = new GaaLineItemsImport;
        Excel::import(new GaaWorkbookImport($import), $file);

        return DB::transaction(function () use ($fiscalYear, $file, $uploader, $referenceNo, $import) {
            $gaa = $this->repository->forFiscalYear($fiscalYear->id)
                ?? $this->repository->create([
                    'fiscal_year_id' => $fiscalYear->id,
                    'title' => "General Appropriations Act FY {$fiscalYear->year}",
                    'status' => GaaStatus::Draft,
                ]);

            abort_if($gaa->status !== GaaStatus::Draft, 422, 'Only a Draft GAA can be re-uploaded. Reject/reset it first.');

            $gaa->lineItems()->delete();

            $gaa->update([
                'reference_no' => $referenceNo,
                'original_filename' => $file->getClientOriginalName(),
                'total_amount' => $import->totalAmount,
                'validation_errors' => $import->errors,
                'uploaded_by' => $uploader->id,
            ]);

            $document = $this->documents->store($file, $gaa, 'fiscal-year', 'source-file', $uploader, [
                'fiscal_year' => $fiscalYear->year,
            ]);
            $gaa->update(['file_disk' => $document->disk, 'file_path' => $document->path]);

            if (! empty($import->rows)) {
                $gaa->lineItems()->createMany($import->rows);
            }

            return $gaa->fresh('lineItems');
        });
    }

    /**
     * "Validate Budget": re-checks the parsed line items and, when clean,
     * advances the GAA to Validated.
     */
    public function validateBudget(GeneralAppropriationsAct $gaa): GaaValidationResult
    {
        $errors = $gaa->validation_errors ?? [];
        $lineItemCount = $gaa->lineItems()->count();

        if ($lineItemCount === 0) {
            $errors[] = 'No valid line items were parsed from the uploaded file.';
        }

        $sum = (float) $gaa->lineItems()->sum('amount');
        if (abs($sum - (float) $gaa->total_amount) > 0.01) {
            $errors[] = 'Sum of line items does not match the computed total amount.';
        }

        if (! empty($errors)) {
            $gaa->update(['validation_errors' => $errors]);

            return GaaValidationResult::failed($errors);
        }

        $gaa->update(['validation_errors' => null]);
        $gaa->transitionTo(GaaStatus::Validated, 'Budget validated: all line items passed reference and totals checks.');

        return GaaValidationResult::passed($sum, $lineItemCount);
    }

    /**
     * "Compare Budget": diff against the prior fiscal year's GAA, grouped
     * by PAP, so Budget Officers can spot large swings before approval.
     */
    public function compareBudget(GeneralAppropriationsAct $gaa): array
    {
        $previousYear = FiscalYear::query()->where('year', $gaa->fiscalYear->year - 1)->first();
        $previousGaa = $previousYear ? $this->repository->forFiscalYear($previousYear->id) : null;

        $current = $gaa->summaryByPap()->pluck('total', 'pap_id');
        $previous = $previousGaa ? $previousGaa->summaryByPap()->pluck('total', 'pap_id') : collect();

        $paps = $current->keys()->merge($previous->keys())->unique();

        return $paps->map(function ($papId) use ($current, $previous) {
            $curr = (float) ($current[$papId] ?? 0);
            $prev = (float) ($previous[$papId] ?? 0);

            return [
                'pap_id' => $papId,
                'current' => $curr,
                'previous' => $prev,
                'variance' => $curr - $prev,
                'variance_pct' => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : null,
            ];
        })->values()->all();
    }

    public function approve(GeneralAppropriationsAct $gaa, User $approver, ?string $remarks = null): GeneralAppropriationsAct
    {
        $gaa->update(['approved_by' => $approver->id, 'approved_at' => now()]);
        $gaa->transitionTo(GaaStatus::Approved, $remarks, action: 'approved');

        $gaa->fiscalYear->update(['total_gaa_amount' => $gaa->total_amount]);

        GaaApproved::dispatch($gaa);

        return $gaa->fresh();
    }

    /**
     * "Generate Budget Allocation": once approved, the GAA's line items
     * seed the top-level (department/division) Budget Allocation pool that
     * Phase 3 further sub-allocates down to offices, cost centers, and
     * PAP/fund-source combinations.
     */
    public function distribute(GeneralAppropriationsAct $gaa, User $user): GeneralAppropriationsAct
    {
        DB::transaction(function () use ($gaa, $user) {
            app(BudgetAllocationService::class)->seedFromGaa($gaa);

            $gaa->update(['distributed_by' => $user->id, 'distributed_at' => now()]);
            $gaa->transitionTo(GaaStatus::Distributed, 'Budget allocation generated and distributed to divisions.', action: 'distributed');
        });

        GaaDistributed::dispatch($gaa->fresh());

        return $gaa->fresh();
    }
}
