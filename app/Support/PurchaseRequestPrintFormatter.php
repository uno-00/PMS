<?php

namespace App\Support;

use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\PurchaseRequestItem;
use App\Models\Settings\AgencyProfile;

/**
 * Maps purchase request data to MIAA F-PMD-PR-1 / Appendix 60 print layout (page 1).
 */
final class PurchaseRequestPrintFormatter
{
    public const MIN_ITEM_ROWS = 15;

    public static function entityName(AgencyProfile $agency): string
    {
        return $agency->displayName();
    }

    public static function fundCluster(PurchaseRequest $pr): string
    {
        $fundSource = $pr->items->first()?->ppmpItem?->fundSource;

        if (! $fundSource) {
            return '—';
        }

        return trim(collect([$fundSource->code, $fundSource->name])->filter()->implode(' — ')) ?: '—';
    }

    public static function officeSection(PurchaseRequest $pr): string
    {
        return $pr->division?->name ?? '—';
    }

    public static function prDate(PurchaseRequest $pr): string
    {
        return ($pr->submitted_at ?? $pr->created_at)?->format('m/d/Y') ?? '—';
    }

    public static function responsibilityCenterCode(PurchaseRequest $pr): string
    {
        $costCenter = $pr->items->first()?->ppmpItem?->budgetAllocation?->costCenter;

        if ($costCenter?->code) {
            return $costCenter->code;
        }

        return $pr->division?->code ?? '—';
    }

    public static function stockPropertyNo(PurchaseRequestItem $item): string
    {
        return (string) ($item->ppmpItem?->item_no ?? '');
    }

    public static function itemDescription(PurchaseRequestItem $item): string
    {
        return trim(collect([$item->item_name, $item->description])->filter()->implode("\n")) ?: '—';
    }

    public static function quantity(PurchaseRequestItem $item): string
    {
        return rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.');
    }

    public static function money(?float $amount): string
    {
        if ($amount === null) {
            return '';
        }

        return number_format($amount, 2);
    }

    /** @return array<int, array<string, string>> */
    public static function itemRows(PurchaseRequest $pr): array
    {
        return $pr->items->map(fn (PurchaseRequestItem $item) => [
            'stock_property_no' => self::stockPropertyNo($item),
            'unit' => $item->unit ?? '',
            'description' => self::itemDescription($item),
            'quantity' => self::quantity($item),
            'unit_cost' => self::money((float) $item->unit_cost),
            'total_cost' => self::money((float) $item->amount),
        ])->values()->all();
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @return array<int, array<string, string>>
     */
    public static function padItemRows(array $rows, int $minimum = self::MIN_ITEM_ROWS): array
    {
        $blank = [
            'stock_property_no' => '',
            'unit' => '',
            'description' => '',
            'quantity' => '',
            'unit_cost' => '',
            'total_cost' => '',
        ];

        while (count($rows) < $minimum) {
            $rows[] = $blank;
        }

        return $rows;
    }

    /** @return array{name: string, designation: string} */
    public static function requestedBy(PurchaseRequest $pr): array
    {
        return [
            'name' => $pr->requestedBy?->name ?? '',
            'designation' => $pr->requestedBy?->position ?? '',
        ];
    }

    /** @return array{name: string, designation: string} */
    public static function approvedBy(PurchaseRequest $pr, AgencyProfile $agency): array
    {
        if ($pr->hopeApprover) {
            return [
                'name' => $pr->hopeApprover->name,
                'designation' => $pr->hopeApprover->position ?? ($agency->hope_position ?? ''),
            ];
        }

        return [
            'name' => $agency->head_of_agency ?? '',
            'designation' => $agency->hope_position ?? 'Head of Procuring Entity',
        ];
    }
}
