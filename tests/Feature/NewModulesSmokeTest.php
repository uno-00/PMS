<?php

namespace Tests\Feature;

use App\Models\Procurement\PurchaseOrder;
use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Support\HelpContent;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke-tests every Livewire page added for the "internal modules" pass
 * (Purchase Orders, Payments, Bidders, Audit Trail, Settings, Reports,
 * Help) so a broken route/view/permission wiring fails CI immediately.
 */
class NewModulesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function userWithRole(string $role): User
    {
        return User::where('email', match ($role) {
            Roles::SUPER_ADMIN => 'superadmin@pms.gov.ph',
            Roles::SUPPLY_OFFICER => 'supply.officer@pms.gov.ph',
            Roles::BAC_SECRETARIAT => 'bac.secretariat@pms.gov.ph',
            Roles::INTERNAL_AUDITOR => 'auditor@pms.gov.ph',
            Roles::CASHIER => 'cashier@pms.gov.ph',
            default => 'superadmin@pms.gov.ph',
        })->firstOrFail();
    }

    public function test_purchase_orders_index_and_show_render_for_supply_officer(): void
    {
        $user = $this->userWithRole(Roles::SUPPLY_OFFICER);

        $this->actingAs($user)->get(route('purchase-orders.index'))->assertOk();

        $po = PurchaseOrder::factory()->create();

        $this->actingAs($user)->get(route('purchase-orders.show', $po))->assertOk();
    }

    public function test_payments_index_renders(): void
    {
        $user = $this->userWithRole(Roles::CASHIER);

        $this->actingAs($user)->get(route('payments.index'))->assertOk();
    }

    public function test_bidder_index_and_show_render_for_bac_secretariat(): void
    {
        $user = $this->userWithRole(Roles::BAC_SECRETARIAT);

        $this->actingAs($user)->get(route('bidders.index'))->assertOk();

        $bidder = Bidder::factory()->create();

        $this->actingAs($user)->get(route('bidders.show', $bidder))->assertOk();
    }

    public function test_audit_trail_renders_for_internal_auditor(): void
    {
        $user = $this->userWithRole(Roles::INTERNAL_AUDITOR);

        $this->actingAs($user)->get(route('audit-trail.index'))->assertOk();
    }

    public function test_reports_index_renders_every_tab_for_super_admin(): void
    {
        $user = $this->userWithRole(Roles::SUPER_ADMIN);

        foreach (['overview', 'budget', 'planning', 'bac', 'suppliers', 'purchases', 'audit', 'analytics'] as $tab) {
            $this->actingAs($user)->get(route('reports.index', ['tab' => $tab]))->assertOk();
        }
    }

    public function test_settings_index_renders_every_tab_for_super_admin(): void
    {
        $user = $this->userWithRole(Roles::SUPER_ADMIN);

        foreach (['appearance', 'profile', 'fiscal-years', 'reference-data', 'security', 'integrations', 'users', 'rbac'] as $tab) {
            $this->actingAs($user)->get(route('settings.index', ['tab' => $tab]))->assertOk();
        }
    }

    public function test_help_index_renders_every_module(): void
    {
        $user = $this->userWithRole(Roles::SUPER_ADMIN);

        foreach (array_keys(HelpContent::modules()) as $module) {
            $this->actingAs($user)->get(route('help.index', ['module' => $module]))->assertOk();
        }
    }

    public function test_non_privileged_user_is_forbidden_from_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);

        $this->actingAs($user)->get(route('settings.index'))->assertForbidden();
    }
}
