<?php

namespace Tests\Unit;

use App\Enums\PpmpStatus;
use App\Enums\PreProcurementConference;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpItem;
use App\Support\PpmpPrintFormatter;
use PHPUnit\Framework\TestCase;

class PpmpPrintFormatterTest extends TestCase
{
    public function test_marks_final_ppmp_when_approved_or_locked(): void
    {
        $approved = new Ppmp(['status' => PpmpStatus::Approved, 'revision_number' => 2]);
        $draft = new Ppmp(['status' => PpmpStatus::Draft, 'revision_number' => 1]);

        $this->assertTrue(PpmpPrintFormatter::isFinal($approved));
        $this->assertFalse(PpmpPrintFormatter::isFinal($draft));
    }

    public function test_project_type_marks_goods_for_supply_items(): void
    {
        $item = new PpmpItem([
            'item_name' => 'Desktop Computers (Core i7, 16GB RAM)',
            'quantity' => 10,
            'unit' => 'unit',
            'specification' => 'Standard desktop configuration',
        ]);

        $this->assertStringContainsString('☑ Goods', PpmpPrintFormatter::projectType($item));
        $this->assertStringContainsString('Quantity: 10 unit', PpmpPrintFormatter::quantityAndSize($item));
    }

    public function test_project_type_marks_consulting_for_tor_items(): void
    {
        $item = new PpmpItem([
            'item_name' => 'Technical Assistance for QMS Implementation',
            'description' => 'Consulting services based on Terms of Reference',
        ]);

        $this->assertStringContainsString('☑ Consulting Services', PpmpPrintFormatter::projectType($item));
    }

    public function test_pre_procurement_conference_uses_explicit_field(): void
    {
        $item = new PpmpItem([
            'pre_procurement_conference' => PreProcurementConference::No,
        ]);

        $this->assertSame('No', PpmpPrintFormatter::preProcurementConference($item));
    }

    public function test_pre_procurement_conference_falls_back_to_remarks(): void
    {
        $item = new PpmpItem([
            'remarks' => 'Pre-Procurement Conference: Yes',
        ]);

        $this->assertSame('Yes', PpmpPrintFormatter::preProcurementConference($item));
    }
}
