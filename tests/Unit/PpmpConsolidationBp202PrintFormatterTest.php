<?php

namespace Tests\Unit;

use App\Support\PpmpConsolidationBp202PrintFormatter;
use PHPUnit\Framework\TestCase;

class PpmpConsolidationBp202PrintFormatterTest extends TestCase
{
    public function test_amount_thousands_formats_zero_and_values(): void
    {
        $this->assertSame('0.', PpmpConsolidationBp202PrintFormatter::amountThousands(0));
        $this->assertSame('500.', PpmpConsolidationBp202PrintFormatter::amountThousands(500000));
        $this->assertSame('12.5.', PpmpConsolidationBp202PrintFormatter::amountThousands(12500));
    }

    public function test_check_mark_returns_x_or_blank(): void
    {
        $this->assertSame('X', PpmpConsolidationBp202PrintFormatter::checkMark(true));
        $this->assertSame('', PpmpConsolidationBp202PrintFormatter::checkMark(false));
    }
}
