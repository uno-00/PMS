<?php

namespace Tests\Feature;

use App\Models\Planning\Ppmp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpmpPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_ppmp_pdf_renders_for_authorized_user(): void
    {
        $ppmp = Ppmp::query()->where('title', 'like', '%FY 2026%')->firstOrFail();
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('ppmps.print', $ppmp))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_ppmp_print_shows_total_budget_and_line_abc(): void
    {
        $ppmp = Ppmp::query()
            ->with(['items.modeOfProcurement', 'items.fundSource', 'items.uacsCode', 'division', 'fiscalYear', 'preparedBy'])
            ->where('title', 'like', '%FY 2026%')
            ->firstOrFail();

        $html = view('pdf.ppmp', [
            'ppmp' => $ppmp,
            'agency' => \App\Models\Settings\AgencyProfile::current(),
            'logoPath' => null,
            'ppmpNumber' => \App\Support\PpmpPrintFormatter::ppmpNumber($ppmp),
            'isFinal' => \App\Support\PpmpPrintFormatter::isFinal($ppmp),
            'formatter' => \App\Support\PpmpPrintFormatter::class,
            'verificationUrl' => null,
        ])->render();

        $this->assertStringContainsString('TOTAL BUDGET', $html);

        $lineTotal = round($ppmp->items->sum(
            fn ($item) => round((float) $item->quantity * (float) $item->estimated_unit_cost, 2)
        ), 2);
        $this->assertStringContainsString(number_format($lineTotal, 2), $html);

        $firstItem = $ppmp->items->first();
        $this->assertNotNull($firstItem);
        $lineAbc = round((float) $firstItem->quantity * (float) $firstItem->estimated_unit_cost, 2);
        $this->assertStringContainsString(number_format($lineAbc, 2), $html);
    }
}
