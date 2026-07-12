<?php

namespace Tests\Feature;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement;
use App\Models\Planning\Ppmp;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PhilgepsCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Build a procurement case that has no PhilGEPS posting yet, reusing
     * the seeded reference data (fiscal year, division, PPMP) so the whole
     * upstream PR chain doesn't need dedicated factories.
     */
    protected function createProcurementWithoutPosting(): Procurement
    {
        $fy = FiscalYear::query()->where('is_current', true)->first()
            ?? FiscalYear::query()->orderByDesc('year')->firstOrFail();

        $ppmp = Ppmp::query()->firstOrCreate(
            ['fiscal_year_id' => $fy->id, 'title' => 'Test PPMP '.Str::random(6)],
            ['division_id' => Division::query()->value('id'), 'status' => 'approved', 'total_abc' => 100000]
        );

        $pr = PurchaseRequest::query()->create([
            'pr_no' => 'PR-TEST-'.strtoupper(Str::random(6)),
            'fiscal_year_id' => $fy->id,
            'division_id' => Division::query()->value('id'),
            'ppmp_id' => $ppmp->id,
            'purpose' => 'Test purchase request.',
            'total_amount' => 50000,
            'status' => 'approved',
        ]);

        return Procurement::query()->create([
            'case_no' => 'BAC-TEST-'.strtoupper(Str::random(6)),
            'purchase_request_id' => $pr->id,
            'title' => 'Test Procurement Case',
            'abc' => 50000,
            'status' => 'planning',
        ]);
    }

    public function test_bac_secretariat_can_view_philgeps_postings(): void
    {
        $user = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('philgeps.index'))
            ->assertOk()
            ->assertSee('PhilGEPS Postings');
    }

    public function test_end_user_cannot_access_philgeps_postings(): void
    {
        $user = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('philgeps.index'))
            ->assertForbidden();
    }

    public function test_secretariat_can_create_a_manual_posting(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $procurement = $this->createProcurementWithoutPosting();

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\PhilgepsForm::class)
            ->set('procurement_id', $procurement->id)
            ->set('reference_no', 'PG-700123')
            ->set('posting_date', now()->format('Y-m-d'))
            ->set('closing_date', now()->addDays(7)->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('philgeps_postings', [
            'procurement_id' => $procurement->id,
            'reference_no' => 'PG-700123',
            'is_manual' => true,
            'posted_by' => $actor->id,
        ]);
    }

    public function test_cannot_create_second_posting_for_same_procurement(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $procurement = $this->createProcurementWithoutPosting();
        PhilgepsPosting::factory()->create([
            'procurement_id' => $procurement->id,
            'is_manual' => true,
            'status' => PhilgepsPostingStatus::Published,
        ]);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\PhilgepsForm::class)
            ->set('procurement_id', $procurement->id)
            ->set('posting_date', now()->format('Y-m-d'))
            ->set('closing_date', now()->addDays(7)->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['procurement_id']);
    }

    public function test_secretariat_can_edit_a_manual_posting(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $procurement = $this->createProcurementWithoutPosting();
        $posting = PhilgepsPosting::factory()->create([
            'procurement_id' => $procurement->id,
            'is_manual' => true,
            'status' => PhilgepsPostingStatus::Published,
        ]);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\PhilgepsForm::class, ['posting' => $posting])
            ->set('reference_no', 'PG-UPDATED-1')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('PG-UPDATED-1', $posting->fresh()->reference_no);
    }

    public function test_secretariat_can_delete_a_manual_posting(): void
    {
        $actor = User::query()->where('email', 'bac.secretariat@pms.gov.ph')->firstOrFail();
        $procurement = $this->createProcurementWithoutPosting();
        $posting = PhilgepsPosting::factory()->create([
            'procurement_id' => $procurement->id,
            'is_manual' => true,
            'status' => PhilgepsPostingStatus::Published,
        ]);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Bac\PhilgepsIndex::class)
            ->call('delete', $posting->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('philgeps_postings', ['id' => $posting->id]);
    }
}
