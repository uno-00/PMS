<?php

namespace Tests\Unit;

use App\Support\MonthYearSchedule;
use PHPUnit\Framework\TestCase;

class MonthYearScheduleTest extends TestCase
{
    public function test_formats_single_month_schedule(): void
    {
        $this->assertSame('March 2026', MonthYearSchedule::format(3, 2026, null, null));
    }

    public function test_formats_month_range_schedule(): void
    {
        $this->assertSame(
            'March 2026 – April 2026',
            MonthYearSchedule::format(3, 2026, 4, 2026),
        );
    }

    public function test_collapses_identical_from_and_to(): void
    {
        $this->assertSame(
            'February 2026',
            MonthYearSchedule::format(2, 2026, 2, 2026),
        );
    }

    public function test_parses_single_month_schedule(): void
    {
        $parsed = MonthYearSchedule::parse('February 2026');

        $this->assertSame(2, $parsed['from_month']);
        $this->assertSame(2026, $parsed['from_year']);
        $this->assertNull($parsed['to_month']);
        $this->assertNull($parsed['to_year']);
    }

    public function test_parses_month_range_schedule(): void
    {
        $parsed = MonthYearSchedule::parse('March 2026 – April 2026');

        $this->assertSame(3, $parsed['from_month']);
        $this->assertSame(2026, $parsed['from_year']);
        $this->assertSame(4, $parsed['to_month']);
        $this->assertSame(2026, $parsed['to_year']);
    }

    public function test_parses_hyphenated_range_schedule(): void
    {
        $parsed = MonthYearSchedule::parse('January 2026 - December 2026');

        $this->assertSame(1, $parsed['from_month']);
        $this->assertSame(12, $parsed['to_month']);
    }

    public function test_month_options_use_full_words(): void
    {
        $this->assertSame('January', MonthYearSchedule::monthOptions()[1]);
        $this->assertSame('December', MonthYearSchedule::monthOptions()[12]);
    }
}
