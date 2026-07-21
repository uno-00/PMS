<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Month-and-year schedule strings for Project Proposal basic info.
 * Stored format: "March 2026" or "March 2026 – April 2026".
 */
final class MonthYearSchedule
{
    /** @return array<int, string> */
    public static function monthOptions(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    /** @return array<int, int> */
    public static function yearOptions(?int $anchorYear = null): array
    {
        $anchor = $anchorYear ?? (int) date('Y');

        return range($anchor - 2, $anchor + 5);
    }

    public static function label(int $month, int $year): string
    {
        $months = self::monthOptions();

        if (! isset($months[$month])) {
            throw new InvalidArgumentException('Invalid month.');
        }

        return $months[$month].' '.$year;
    }

    public static function format(
        int|string|null $fromMonth,
        int|string|null $fromYear,
        int|string|null $toMonth,
        int|string|null $toYear,
    ): ?string {
        $fromMonth = self::normalizePart($fromMonth);
        $fromYear = self::normalizePart($fromYear);
        $toMonth = self::normalizePart($toMonth);
        $toYear = self::normalizePart($toYear);

        if ($fromMonth === null && $fromYear === null && $toMonth === null && $toYear === null) {
            return null;
        }

        if ($fromMonth === null || $fromYear === null) {
            return null;
        }

        $from = self::label($fromMonth, $fromYear);

        if ($toMonth === null || $toYear === null) {
            return $from;
        }

        $to = self::label($toMonth, $toYear);

        if ($fromMonth === $toMonth && $fromYear === $toYear) {
            return $from;
        }

        return $from.' – '.$to;
    }

    /**
     * @return array{from_month: ?int, from_year: ?int, to_month: ?int, to_year: ?int}
     */
    public static function parse(?string $schedule): array
    {
        $empty = [
            'from_month' => null,
            'from_year' => null,
            'to_month' => null,
            'to_year' => null,
        ];

        if ($schedule === null || trim($schedule) === '') {
            return $empty;
        }

        $parts = preg_split('/\s+[–—-]\s+/u', trim($schedule)) ?: [];
        $from = self::parseLabel($parts[0] ?? '');
        $to = self::parseLabel($parts[1] ?? '');

        if ($from === null) {
            return $empty;
        }

        if ($to === null) {
            return [
                'from_month' => $from['month'],
                'from_year' => $from['year'],
                'to_month' => null,
                'to_year' => null,
            ];
        }

        return [
            'from_month' => $from['month'],
            'from_year' => $from['year'],
            'to_month' => $to['month'],
            'to_year' => $to['year'],
        ];
    }

    /**
     * @param  callable(string): void  $addError
     */
    public static function validateRange(
        int|string|null $fromMonth,
        int|string|null $fromYear,
        int|string|null $toMonth,
        int|string|null $toYear,
        callable $addError,
    ): void {
        $fromMonth = self::normalizePart($fromMonth);
        $fromYear = self::normalizePart($fromYear);
        $toMonth = self::normalizePart($toMonth);
        $toYear = self::normalizePart($toYear);

        $hasFrom = $fromMonth !== null || $fromYear !== null;
        $hasTo = $toMonth !== null || $toYear !== null;

        if (! $hasFrom && ! $hasTo) {
            return;
        }

        if ($hasFrom && ($fromMonth === null || $fromYear === null)) {
            $addError('Select both month and year for the schedule start.');

            return;
        }

        if ($hasTo && ($toMonth === null || $toYear === null)) {
            $addError('Select both month and year for the schedule end.');

            return;
        }

        if ($hasTo && ! $hasFrom) {
            $addError('Select a schedule start before choosing an end date.');

            return;
        }

        if ($hasFrom && $hasTo && self::compare($fromMonth, $fromYear, $toMonth, $toYear) > 0) {
            $addError('Schedule end must be the same as or after the start date.');
        }
    }

    public static function compare(int $leftMonth, int $leftYear, int $rightMonth, int $rightYear): int
    {
        $left = ($leftYear * 12) + $leftMonth;
        $right = ($rightYear * 12) + $rightMonth;

        return $left <=> $right;
    }

    /** @return array{month: int, year: int}|null */
    protected static function parseLabel(string $label): ?array
    {
        $label = trim($label);

        if ($label === '') {
            return null;
        }

        if (preg_match('/^([A-Za-z]+)\s+(\d{4})$/', $label, $matches) !== 1) {
            return null;
        }

        $normalized = strtolower($matches[1]);

        foreach (self::monthOptions() as $month => $name) {
            if (strtolower($name) === $normalized) {
                return ['month' => $month, 'year' => (int) $matches[2]];
            }
        }

        return null;
    }

    protected static function normalizePart(int|string|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
