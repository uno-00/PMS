<?php

namespace App\Support;

use App\Enums\PpmpDocumentType;
use App\Enums\PpmpProjectType;
use App\Enums\PreProcurementConference;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Planning\PpmpConsolidationItem;
use App\Support\RichTextSanitizer;
use Carbon\CarbonInterface;

/**
 * Maps consolidated PPMP line items to the same NGPA print layout as division PPMPs.
 */
final class PpmpConsolidationPrintFormatter
{
    public static function ppmpNumber(PpmpConsolidation $consolidation): int
    {
        return max(1, (int) $consolidation->version_number);
    }

    public static function isFinal(PpmpConsolidation $consolidation): bool
    {
        return ($consolidation->document_type ?? PpmpDocumentType::Indicative) === PpmpDocumentType::Final;
    }

    public static function generalDescription(PpmpConsolidationItem $item): string
    {
        $parts = array_filter([
            $item->item_name,
            self::plain($item->description),
            $item->division?->name ? 'End-User / Implementing Unit: '.$item->division->name : null,
        ]);

        return implode("\n", $parts) ?: '—';
    }

    public static function projectType(PpmpConsolidationItem $item): string
    {
        $category = $item->project_type instanceof PpmpProjectType
            ? $item->project_type->value
            : self::resolveCategory($item);

        $marks = fn (string $key) => $category === $key ? '☑' : '☐';

        return implode("\n", [
            $marks('goods').' Goods',
            $marks('infrastructure').' Infrastructure',
            $marks('consulting').' Consulting Services',
        ]);
    }

    public static function quantityAndSize(PpmpConsolidationItem $item): string
    {
        $quantity = rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.');
        $specification = self::plain($item->specification);

        $lines = array_filter([
            $quantity && $item->unit ? "Quantity: {$quantity} {$item->unit}" : ($quantity ? "Quantity: {$quantity}" : null),
            $specification !== '' ? 'Size: '.$specification : null,
        ]);

        return implode("\n", $lines) ?: '—';
    }

    public static function modeOfProcurement(PpmpConsolidationItem $item): string
    {
        return $item->modeOfProcurement?->name ?? '—';
    }

    public static function preProcurementConference(PpmpConsolidationItem $item): string
    {
        if ($item->pre_procurement_conference instanceof PreProcurementConference) {
            return $item->pre_procurement_conference->label();
        }

        $remarks = strtolower($item->remarks ?? '');

        if (str_contains($remarks, 'pre-procurement conference: yes')) {
            return 'Yes';
        }

        if (str_contains($remarks, 'pre-procurement conference: no')) {
            return 'No';
        }

        $mode = strtolower($item->modeOfProcurement?->name ?? '');

        if (str_contains($mode, 'administration')) {
            return 'N/A';
        }

        return str_contains($mode, 'competitive bidding') ? 'Yes' : 'No';
    }

    public static function monthYear(?CarbonInterface $date): string
    {
        return $date?->format('m/Y') ?? '—';
    }

    public static function deliveryPeriod(PpmpConsolidationItem $item): string
    {
        if ($item->schedule_start && $item->schedule_end) {
            return self::monthYear($item->schedule_start).' to '.self::monthYear($item->schedule_end);
        }

        return self::monthYear($item->schedule_end ?: $item->schedule_start);
    }

    public static function sourceOfFunds(PpmpConsolidationItem $item): string
    {
        return $item->fundSource?->name ?? '—';
    }

    public static function budgetAmount(PpmpConsolidationItem $item): string
    {
        return '₱'.number_format($item->lineAbc(), 2);
    }

    public static function totalBudget(PpmpConsolidation $consolidation): string
    {
        $total = $consolidation->items->sum(fn (PpmpConsolidationItem $item) => $item->lineAbc());

        return '₱'.number_format(round((float) $total, 2), 2);
    }

    public static function supportingDocuments(PpmpConsolidationItem $item): string
    {
        $docs = ['Market Scoping Checklist'];

        if (self::plain($item->specification) !== '') {
            $docs[] = 'Technical Specifications';
        }

        if (self::plain($item->description) !== '') {
            $docs[] = 'Scope of Work / Terms of Reference';
        }

        return implode("\n", $docs);
    }

    public static function remarks(PpmpConsolidationItem $item): string
    {
        $parts = array_filter([
            self::plain($item->remarks) ?: null,
            $item->is_merged ? 'Merged from '.count($item->source_ppmp_item_ids ?? []).' source item(s).' : null,
        ]);

        return implode("\n", $parts) ?: '—';
    }

    protected static function plain(?string $value): string
    {
        return RichTextSanitizer::plainText($value);
    }

    protected static function resolveCategory(PpmpConsolidationItem $item): string
    {
        $haystack = strtolower(implode(' ', array_filter([
            $item->item_name,
            self::plain($item->description),
            self::plain($item->specification),
            $item->relationLoaded('uacsCode') ? $item->uacsCode?->description : null,
        ])));

        if (preg_match('/\b(consulting|consultancy|technical assistance|tor\b|terms of reference)\b/', $haystack)) {
            return 'consulting';
        }

        if (preg_match('/\b(infrastructure|construction|repair|improvement|building|road|bridge|facility|civil works)\b/', $haystack)) {
            return 'infrastructure';
        }

        return 'goods';
    }
}
