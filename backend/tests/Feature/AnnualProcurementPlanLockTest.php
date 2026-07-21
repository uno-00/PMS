<?php

namespace Tests\Feature;

use App\Enums\AnnualProcurementPlanStatus;
use App\Livewire\Planning\AppShow;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnnualProcurementPlanLockTest extends TestCase
{
    use RefreshDatabase;

    protected AnnualProcurementPlan $annualPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $fiscalYear = FiscalYear::query()->where('is_current', true)->firstOrFail();

        $this->annualPlan = AnnualProcurementPlan::query()
            ->where('fiscal_year_id', $fiscalYear->id)
            ->firstOrFail();

        $this->annualPlan->update(['status' => AnnualProcurementPlanStatus::Approved]);
    }

    public function test_hope_can_lock_and_unlock_app(): void
    {
        $user = User::where('email', 'hope@pms.gov.ph')->firstOrFail();

        $this->actingAs($user);

        Livewire::test(AppShow::class, ['fiscalYear' => $this->annualPlan->fiscalYear])
            ->call('lock')
            ->assertHasNoErrors();

        $this->annualPlan->refresh();
        $this->assertSame(AnnualProcurementPlanStatus::Locked, $this->annualPlan->status);
        $this->assertNotNull($this->annualPlan->locked_at);

        Livewire::test(AppShow::class, ['fiscalYear' => $this->annualPlan->fiscalYear])
            ->call('unlock')
            ->assertHasNoErrors();

        $this->annualPlan->refresh();
        $this->assertSame(AnnualProcurementPlanStatus::Approved, $this->annualPlan->status);
        $this->assertNull($this->annualPlan->locked_at);
    }

    public function test_viewer_cannot_lock_app(): void
    {
        $user = User::where('email', 'viewer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user);

        Livewire::test(AppShow::class, ['fiscalYear' => $this->annualPlan->fiscalYear])
            ->call('lock')
            ->assertForbidden();
    }
}
