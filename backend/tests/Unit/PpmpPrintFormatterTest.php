<?php

namespace Tests\Unit;

use App\Enums\PpmpDocumentType;
use App\Enums\PpmpProjectType;
use App\Enums\PreProcurementConference;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpItem;
use App\Support\PpmpPrintFormatter;
use PHPUnit\Framework\TestCase;

class PpmpPrintFormatterTest extends TestCase
{
    public function test_marks_final_ppmp_from_document_type(): void
    {
        $final = new Ppmp(['document_type' => PpmpDocumentType::Final, 'revision_number' => 2]);
        $indicative = new Ppmp(['document_type' => PpmpDocumentType::Indicative, 'revision_number' => 1]);

        $this->assertTrue(PpmpPrintFormatter::isFinal($final));
        $this->assertFalse(PpmpPrintFormatter::isFinal($indicative));
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

    public function test_project_type_uses_stored_value_when_set(): void
    {
        $item = new PpmpItem([
            'project_type' => PpmpProjectType::Infrastructure,
            'item_name' => 'Technical Assistance for QMS Implementation',
            'description' => 'Consulting services based on Terms of Reference',
        ]);

        $this->assertStringContainsString('☑ Infrastructure', PpmpPrintFormatter::projectType($item));
        $this->assertStringContainsString('☐ Consulting Services', PpmpPrintFormatter::projectType($item));
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

    public function test_print_output_strips_html_from_item_fields(): void
    {
        $item = new PpmpItem([
            'item_name' => 'Desktop Computer Refresh',
            'description' => '<p>Replace aging <strong>workstations</strong>.</p>',
            'specification' => '<ol><li>Acquire 20 units</li></ol>',
            'quantity' => 1,
            'unit' => 'Lot',
            'remarks' => '<p>Breakdown of <strong>estimated costs</strong>.</p>',
        ]);

        $description = PpmpPrintFormatter::generalDescription($item);

        $this->assertStringNotContainsString('<p>', $description);
        $this->assertStringContainsString('Replace aging workstations.', $description);
        $this->assertStringNotContainsString('<ol>', PpmpPrintFormatter::quantityAndSize($item));
        $this->assertStringContainsString('Acquire 20 units', PpmpPrintFormatter::quantityAndSize($item));
        $this->assertSame('Breakdown of estimated costs.', PpmpPrintFormatter::remarks($item));
    }

    public function test_budget_amount_uses_line_abc_not_stored_total(): void
    {
        $item = new PpmpItem([
            'quantity' => 3,
            'estimated_unit_cost' => 1500,
            'abc' => 999999,
        ]);

        $this->assertSame(4500.0, PpmpPrintFormatter::lineAbc($item));
        $this->assertSame('₱4,500.00', PpmpPrintFormatter::budgetAmount($item));
    }

    public function test_total_budget_sums_line_abc_not_stored_ppmp_total(): void
    {
        $ppmp = new Ppmp(['total_abc' => 999999]);
        $ppmp->setRelation('items', collect([
            new PpmpItem(['quantity' => 2, 'estimated_unit_cost' => 1000, 'abc' => 5000]),
            new PpmpItem(['quantity' => 1, 'estimated_unit_cost' => 500, 'abc' => 8000]),
        ]));

        $this->assertSame('₱2,500.00', PpmpPrintFormatter::totalBudget($ppmp));
    }
}
