<?php

namespace Tests\Feature;

use App\Enums\PpmpConsolidationStatus;
use App\Enums\PpmpDocumentType;
use App\Enums\PpmpStatus;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpConsolidation;
use App\Models\User;
use App\Services\Planning\PpmpConsolidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpmpConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_planning_officer_can_view_consolidation_index(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('ppmp-consolidations.index'))
            ->assertOk();
    }

    public function test_planning_officer_can_open_consolidation_wizard(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('ppmp-consolidations.create'))
            ->assertOk();
    }

    public function test_service_merges_selected_ppmps_into_consolidated_items(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();
        $service = app(PpmpConsolidationService::class);

        $ppmps = Ppmp::query()
            ->where('document_type', PpmpDocumentType::Indicative)
            ->whereIn('status', [PpmpStatus::Approved, PpmpStatus::Locked])
            ->limit(2)
            ->get();

        if ($ppmps->count() < 2) {
            $this->markTestSkipped('Need at least two approved indicative PPMPs in seed data.');
        }

        $consolidation = $service->createDraft(
            $user,
            $ppmps->first()->fiscal_year_id,
            PpmpDocumentType::Indicative,
            $ppmps->pluck('id')->all(),
        );

        $service->generateConsolidatedItems($consolidation);
        $service->generateBp2020($consolidation);
        $service->generateWfp($consolidation);

        $consolidation->refresh()->load(['items', 'bp2020Lines', 'wfpLines']);

        $this->assertGreaterThan(0, $consolidation->items->count());
        $this->assertSame(
            round((float) $consolidation->items->sum(fn ($item) => $item->lineAbc()), 2),
            round((float) $consolidation->total_budget, 2),
        );
        $this->assertSame($consolidation->items->count(), $consolidation->bp2020Lines->count());
        $this->assertSame($consolidation->items->count(), $consolidation->wfpLines->count());
    }

    public function test_consolidated_ppmp_pdf_uses_ngpa_ppmp_layout(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();
        $service = app(PpmpConsolidationService::class);

        $ppmp = Ppmp::query()
            ->whereIn('status', [PpmpStatus::Approved, PpmpStatus::Locked])
            ->firstOrFail();

        $consolidation = $service->createDraft(
            $user,
            $ppmp->fiscal_year_id,
            $ppmp->document_type ?? PpmpDocumentType::Indicative,
            [$ppmp->id],
        );
        $service->generateConsolidatedItems($consolidation);

        $html = view('pdf.ppmp-consolidation', [
            'consolidation' => $consolidation->load(['items.division', 'items.modeOfProcurement', 'items.fundSource', 'sourcePpmps', 'fiscalYear', 'creator']),
            'agency' => \App\Models\Settings\AgencyProfile::current(),
            'logoPath' => null,
            'ppmpNumber' => \App\Support\PpmpConsolidationPrintFormatter::ppmpNumber($consolidation),
            'isFinal' => \App\Support\PpmpConsolidationPrintFormatter::isFinal($consolidation),
            'formatter' => \App\Support\PpmpConsolidationPrintFormatter::class,
        ])->render();

        $this->assertStringContainsString('Procurement Project Details', $html);
        $this->assertStringContainsString('TOTAL BUDGET:', $html);
        $this->assertStringContainsString('Type of the Project to be Procured', $html);

        $this->actingAs($user)
            ->get(route('ppmp-consolidations.print', $consolidation))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_super_admin_can_cancel_consolidation(): void
    {
        $planning = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();
        $admin = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Super Admin'))->firstOrFail();
        $service = app(PpmpConsolidationService::class);

        $ppmp = Ppmp::query()
            ->whereIn('status', [PpmpStatus::Approved, PpmpStatus::Locked])
            ->firstOrFail();

        $consolidation = $service->createDraft(
            $planning,
            $ppmp->fiscal_year_id,
            $ppmp->document_type ?? PpmpDocumentType::Indicative,
            [$ppmp->id],
        );

        $service->cancel($consolidation, $admin, 'Cancelled for testing purposes.');

        $consolidation->refresh();
        $this->assertSame(PpmpConsolidationStatus::Cancelled, $consolidation->status);
    }

    public function test_bp202_pdf_uses_issp_profile_layout(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();
        $service = app(PpmpConsolidationService::class);

        $ppmp = Ppmp::query()
            ->whereIn('status', [PpmpStatus::Approved, PpmpStatus::Locked])
            ->firstOrFail();

        $consolidation = $service->createDraft(
            $user,
            $ppmp->fiscal_year_id,
            $ppmp->document_type ?? PpmpDocumentType::Indicative,
            [$ppmp->id],
        );
        $service->generateConsolidatedItems($consolidation);
        $service->generateBp2020($consolidation);

        $agency = \App\Models\Settings\AgencyProfile::current();
        $html = view('pdf.ppmp-consolidation-bp2020', [
            'consolidation' => $consolidation->load(['bp2020Lines.consolidationItem.division', 'bp2020Lines.consolidationItem.pap', 'fiscalYear']),
            'agency' => $agency,
            'formTitle' => \App\Support\PpmpConsolidationBp202PrintFormatter::formTitle($consolidation),
            'profiles' => \App\Support\PpmpConsolidationBp202PrintFormatter::profiles($consolidation, $agency),
            'formatter' => \App\Support\PpmpConsolidationBp202PrintFormatter::class,
        ])->render();

        $this->assertStringContainsString('PROFILE FOR LOCALLY-FUNDED PROJECTS', $html);
        $this->assertStringContainsString('12.1. PAP ATTRIBUTION BY EXPENSE CLASS', $html);
        $this->assertStringContainsString('12.6. LOCATION OF IMPLEMENTATION', $html);
        $this->assertStringContainsString('Prepared and Certified Correct:', $html);

        $this->actingAs($user)
            ->get(route('ppmp-consolidations.bp2020.print', $consolidation))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_prevents_non_approved_ppmp_selection(): void
    {
        $user = User::query()->where('email', 'planning.officer@pms.gov.ph')->firstOrFail();
        $service = app(PpmpConsolidationService::class);

        $draftPpmp = Ppmp::query()->where('status', PpmpStatus::Draft)->first();

        if (! $draftPpmp) {
            $this->markTestSkipped('No draft PPMP in seed data.');
        }

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $service->createDraft(
            $user,
            $draftPpmp->fiscal_year_id,
            PpmpDocumentType::Indicative,
            [$draftPpmp->id],
        );
    }
}
