<?php

namespace App\Support;

use App\Enums\MarketScopingConsidered;
use App\Models\Planning\MarketScoping;

/**
 * Maps Market Scoping database fields to the GPPB NGPA Market Scoping Checklist print layout.
 */
final class MarketScopingPrintFormatter
{
    /** @return array<int, array{key: string, label: string, documentation_hint: string}> */
    public static function activityDefinitions(): array
    {
        return [
            [
                'key' => 'consultations',
                'label' => 'Consultations with suppliers / contractors / consultants / professional associations or industry groups',
                'documentation_hint' => 'Highlights of consultations or meetings / Proof of attendance / Reports / Summaries / Screenshots / Brochures / Publications / Price quotations / Canvass sheets / Market Analysis Report or similar document/s',
            ],
            [
                'key' => 'summits',
                'label' => 'Participation in summits, fora, or conferences',
                'documentation_hint' => 'Highlights of consultations or meetings / Proof of Attendance / Reports',
            ],
            [
                'key' => 'reports',
                'label' => 'Review of technical, financial, or market/scientific reports',
                'documentation_hint' => 'Reports / Summaries / Screenshots / Brochures / Publications, Market Analysis Report or similar document / Online Product Reviews',
            ],
            [
                'key' => 'brochures',
                'label' => 'Review of product or service brochures, marketing materials, industry journals and publications or related materials',
                'documentation_hint' => 'Reports / Summaries / Screenshots / Brochures / Publications / Online Product Reviews',
            ],
            [
                'key' => 'price_sourcing',
                'label' => 'Price sourcing for quotations or cost estimates from suppliers, contractors, or consultants',
                'documentation_hint' => 'Price quotations / Canvass sheets / Online Product Reviews',
            ],
            [
                'key' => 'philgeps',
                'label' => 'Use of data from PhilGEPS or agency websites',
                'documentation_hint' => 'Reports / Summaries / Screenshots, Price quotations / Canvass sheets / PhilGEPS Postings / Online Product Reviews',
            ],
            [
                'key' => 'other',
                'label' => 'Other analogous market scoping activity/ies undertaken',
                'documentation_hint' => '',
            ],
        ];
    }

    /** @return array<int, array{key: string, label: string, letter: string}> */
    public static function parameterDefinitions(): array
    {
        return [
            ['key' => 'cost_estimate', 'letter' => 'a', 'label' => 'Project Cost Estimate [Does the cost estimate align with current market prices?]'],
            ['key' => 'design_spec', 'letter' => 'b', 'label' => 'Project Design and Specification [Does available supplier/s meet technical and financial requirements?]'],
            ['key' => 'technical_criteria', 'letter' => 'c', 'label' => 'Technical Criteria [Does the market support the proposed technical requirements?]'],
            ['key' => 'delivery_lead_time', 'letter' => 'd', 'label' => 'Delivery Lead Time [Are the timelines for delivery feasible?]'],
            ['key' => 'storage_warehousing', 'letter' => 'e', 'label' => 'Storage and Warehousing Requirements [Can the storage/warehousing needs be met considering specific conditions like temperature, humidity, and handling?]'],
            ['key' => 'risks', 'letter' => 'f', 'label' => 'Identified Risk/s [Were there any market risks identified? (e.g., limited suppliers, price volatility)]'],
        ];
    }

    public static function activityChecked(MarketScoping $record, string $key): bool
    {
        return (bool) data_get($record->activities, "{$key}.checked", false);
    }

    public static function activityDocumentation(MarketScoping $record, string $key): string
    {
        if ($key === 'other') {
            $description = data_get($record->activities, 'other.description', '');

            return trim((string) data_get($record->activities, "{$key}.documentation", '').($description ? "\n{$description}" : '')) ?: '—';
        }

        return data_get($record->activities, "{$key}.documentation") ?: '—';
    }

    public static function parameterConsidered(MarketScoping $record, string $key): string
    {
        $value = data_get($record->parameters, "{$key}.considered");

        if (! $value) {
            return '—';
        }

        return MarketScopingConsidered::tryFrom($value)?->label() ?? '—';
    }

    public static function parameterRecommendations(MarketScoping $record, string $key): string
    {
        return data_get($record->parameters, "{$key}.recommendations") ?: '—';
    }

    public static function monthYear(?\Carbon\CarbonInterface $date): string
    {
        return $date?->format('m/Y') ?? '—';
    }

    public static function periodRange(MarketScoping $record): string
    {
        if ($record->period_from && $record->period_to) {
            return self::monthYear($record->period_from).' to '.self::monthYear($record->period_to);
        }

        return self::monthYear($record->period_from ?: $record->period_to);
    }

    public static function checkMark(bool $checked): string
    {
        return $checked ? '☑' : '☐';
    }
}
