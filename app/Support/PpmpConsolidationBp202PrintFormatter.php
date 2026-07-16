<?php

namespace App\Support;

use App\Enums\PpmpExpenseClass;
use App\Enums\PpmpProjectType;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Planning\PpmpConsolidationBp2020Line;
use App\Models\Settings\AgencyProfile;
use App\Support\RichTextSanitizer;

class PpmpConsolidationBp202PrintFormatter
{
    /** @return array{tier2: string, year1: string, year2: string, budget_year: int} */
    public static function fiscalColumns(PpmpConsolidation $consolidation): array
    {
        $budgetYear = (int) ($consolidation->fiscalYear?->year ?? now()->year);

        return [
            'budget_year' => $budgetYear,
            'tier2' => 'FY '.($budgetYear - 2).' TIER2',
            'year1' => (string) ($budgetYear - 1),
            'year2' => (string) $budgetYear,
        ];
    }

    public static function formTitle(PpmpConsolidation $consolidation): string
    {
        $year = (int) ($consolidation->fiscalYear?->year ?? now()->year);

        return 'Revised BP FORM 202 ('.$year.' Budget Tier 2)';
    }

    /** @return array<int, array<string, mixed>> */
    public static function profiles(PpmpConsolidation $consolidation, AgencyProfile $agency): array
    {
        return $consolidation->bp2020Lines
            ->sortBy('sort_order')
            ->values()
            ->map(fn (PpmpConsolidationBp2020Line $line, int $index) => self::profile($line, $consolidation, $agency, $index + 1))
            ->all();
    }

    /** @return array<string, mixed> */
    public static function profile(
        PpmpConsolidationBp2020Line $line,
        PpmpConsolidation $consolidation,
        AgencyProfile $agency,
        int $priority,
    ): array {
        $item = $line->consolidationItem;
        $columns = self::fiscalColumns($consolidation);
        $amount = (float) $line->budget_allocation;
        $amountThousands = self::amountThousands($amount);
        $expenseClass = $item?->expense_class;
        $projectType = $item?->project_type;
        $isInfrastructure = $projectType === PpmpProjectType::Infrastructure;
        $isMooe = $expenseClass === PpmpExpenseClass::Mooe;
        $isCo = $expenseClass === PpmpExpenseClass::CapitalOutlay;
        $papName = $line->program ?: ($item?->pap?->name ?: 'General management and supervision');
        $location = $line->activity ?: ($item?->division?->name ?: 'Central Office');

        return [
            'project_name' => $line->procurement_item,
            'implementing_agency' => $agency->displayName(),
            'priority_ranking' => (string) $priority,
            'is_new' => true,
            'is_expanded' => false,
            'is_infrastructure' => $isInfrastructure,
            'is_non_infrastructure' => ! $isInfrastructure,
            'pip_code' => $item?->pap?->code ?? '',
            'total_cost_thousands' => $amountThousands,
            'description' => RichTextSanitizer::plainText($item?->description ?? $line->procurement_item),
            'purpose' => RichTextSanitizer::plainText($item?->specification ?? ''),
            'beneficiaries' => 'Agency employees and stakeholders',
            'original_start' => optional($item?->schedule_start)->format('m/d/Y') ?? '',
            'original_finish' => optional($item?->schedule_end)->format('m/d/Y') ?? '',
            'revised_start' => '',
            'revised_finish' => '',
            'pap_name' => $papName,
            'pap_tier2' => self::amountThousands(0),
            'pap_year1' => self::amountThousands(0),
            'pap_year2' => self::amountThousands(0),
            'co_tier2' => $isCo ? self::amountThousands(0) : self::amountThousands(0),
            'co_year1' => self::amountThousands(0),
            'co_year2' => $isCo ? $amountThousands : self::amountThousands(0),
            'mooe_tier2' => self::amountThousands(0),
            'mooe_year1' => self::amountThousands(0),
            'mooe_year2' => $isMooe ? $amountThousands : self::amountThousands(0),
            'grand_tier2' => self::amountThousands(0),
            'grand_year1' => self::amountThousands(0),
            'grand_year2' => $amountThousands,
            'physical_description' => $line->procurement_item,
            'physical_tier2' => self::amountThousands(0),
            'physical_year1' => self::amountThousands(0),
            'physical_year2' => self::amountThousands(0),
            'total_mooe' => $isMooe ? $amountThousands : self::amountThousands(0),
            'total_co' => $isCo ? $amountThousands : self::amountThousands(0),
            'total_grand' => $amountThousands,
            'component_name' => $line->procurement_item,
            'component_ps' => self::amountThousands(0),
            'component_mooe' => $isMooe ? $amountThousands : self::amountThousands(0),
            'component_co' => $isCo ? $amountThousands : self::amountThousands(0),
            'component_finex' => self::amountThousands(0),
            'component_total' => $amountThousands,
            'location' => $location,
            'location_ps' => self::amountThousands(0),
            'location_mooe' => $isMooe ? $amountThousands : self::amountThousands(0),
            'location_co' => $isCo ? $amountThousands : self::amountThousands(0),
            'location_finex' => self::amountThousands(0),
            'location_total' => $amountThousands,
            'fund_source' => $line->fund_source ?? ($item?->fundSource?->name ?? ''),
            'columns' => $columns,
        ];
    }

    public static function amountThousands(float $amount): string
    {
        if ($amount <= 0) {
            return '0.';
        }

        $thousands = round($amount / 1000, 1);

        return rtrim(rtrim(number_format($thousands, 1, '.', ''), '0'), '.').'.';
    }

    public static function checkMark(bool $checked): string
    {
        return $checked ? 'X' : '';
    }
}
