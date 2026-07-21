<?php

namespace Tests\Unit;

use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\PurchaseRequestItem;
use App\Models\Settings\AgencyProfile;
use App\Models\Settings\Division;
use App\Support\PurchaseRequestPrintFormatter;
use PHPUnit\Framework\TestCase;

class PurchaseRequestPrintFormatterTest extends TestCase
{
    public function test_pads_item_rows_to_minimum(): void
    {
        $rows = PurchaseRequestPrintFormatter::padItemRows([
            ['stock_property_no' => '1', 'unit' => 'unit', 'description' => 'Test', 'quantity' => '1', 'unit_cost' => '100.00', 'total_cost' => '100.00'],
        ]);

        $this->assertCount(PurchaseRequestPrintFormatter::MIN_ITEM_ROWS, $rows);
        $this->assertSame('1', $rows[0]['stock_property_no']);
        $this->assertSame('', $rows[1]['description']);
    }

    public function test_resolves_responsibility_center_from_division_code(): void
    {
        $pr = new PurchaseRequest;
        $pr->setRelation('items', collect());
        $pr->setRelation('division', new Division(['code' => 'ITS-001', 'name' => 'ITS']));

        $this->assertSame('ITS-001', PurchaseRequestPrintFormatter::responsibilityCenterCode($pr));
    }

    public function test_formats_item_description_with_name_and_details(): void
    {
        $item = new PurchaseRequestItem([
            'item_name' => 'Desktop Computers',
            'description' => 'Core i7, 16GB RAM',
        ]);

        $this->assertStringContainsString('Desktop Computers', PurchaseRequestPrintFormatter::itemDescription($item));
        $this->assertStringContainsString('Core i7', PurchaseRequestPrintFormatter::itemDescription($item));
    }

    public function test_approved_by_falls_back_to_agency_head(): void
    {
        $pr = new PurchaseRequest;
        $pr->setRelation('hopeApprover', null);

        $agency = new AgencyProfile([
            'head_of_agency' => 'Maria Santos',
            'hope_position' => 'General Manager',
        ]);

        $approved = PurchaseRequestPrintFormatter::approvedBy($pr, $agency);

        $this->assertSame('Maria Santos', $approved['name']);
        $this->assertSame('General Manager', $approved['designation']);
    }
}
