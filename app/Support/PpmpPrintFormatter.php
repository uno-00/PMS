<?php

namespace App\Support;

use App\Enums\PpmpDocumentType;
use App\Enums\PreProcurementConference;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpItem;
use App\Support\RichTextSanitizer;
use Carbon\CarbonInterface;

/**
 * Maps PPMP database fields to the NGPA PPMP print layout
 * (GPPB / RA 12009 IRR Sections 7.7.1–7.7.3).
 */
final class PpmpPrintFormatter
{
    public static function ppmpNumber(Ppmp $ppmp): int
    {
        return max(1, (int) $ppmp->revision_number);
    }

    public static function isFinal(Ppmp $ppmp): bool
    {
        return ($ppmp->document_type ?? PpmpDocumentType::Indicative) === PpmpDocumentType::Final;
    }

    public static function isIndicative(Ppmp $ppmp): bool
    {
        return ! self::isFinal($ppmp);
    }

    public static function generalDescription(PpmpItem $item): string
    {
        $parts = array_filter([
            $item->item_name,
            self::plain($item->description),
        ]);

        return implode("\n", $parts) ?: '—';
    }

    public static function projectType(PpmpItem $item): string
    {
        $category = self::resolveCategory($item);

        $marks = fn (string $key) => $category === $key ? '☑' : '☐';

        return implode("\n", [
            $marks('goods').' Goods',
            $marks('infrastructure').' Infrastructure',
            $marks('consulting').' Consulting Services',
        ]);
    }

    public static function quantityAndSize(PpmpItem $item): string
    {
        $quantity = rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.');
        $specification = self::plain($item->specification);

        $lines = array_filter([
            $quantity && $item->unit ? "Quantity: {$quantity} {$item->unit}" : ($quantity ? "Quantity: {$quantity}" : null),
            $specification !== '' ? 'Size: '.$specification : null,
        ]);

        return implode("\n", $lines) ?: '—';
    }

    public static function modeOfProcurement(PpmpItem $item): string
    {
        return $item->modeOfProcurement?->name ?? '—';
    }

    public static function preProcurementConference(PpmpItem $item): string
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

    public static function deliveryPeriod(PpmpItem $item): string
    {
        if ($item->schedule_start && $item->schedule_end) {
            return self::monthYear($item->schedule_start).' to '.self::monthYear($item->schedule_end);
        }

        return self::monthYear($item->schedule_end ?: $item->schedule_start);
    }

    public static function sourceOfFunds(PpmpItem $item): string
    {
        return $item->fundSource?->name ?? '—';
    }

    public static function budgetAmount(PpmpItem $item): string
    {
        return '₱'.number_format((float) $item->abc, 2);
    }

    public static function supportingDocuments(PpmpItem $item): string
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

    public static function remarks(PpmpItem $item): string
    {
        return self::plain($item->remarks) ?: '—';
    }

    protected static function plain(?string $value): string
    {
        return RichTextSanitizer::plainText($value);
    }

    protected static function resolveCategory(PpmpItem $item): string
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
