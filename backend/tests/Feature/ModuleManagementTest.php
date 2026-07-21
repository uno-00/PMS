<?php

namespace Tests\Feature;

use App\Models\Settings\SystemSetting;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_super_admin_can_view_module_management_tab(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ModulesTab::class)
            ->assertOk()
            ->assertSee('Module Management');
    }

    public function test_super_admin_can_deactivate_a_module(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ModulesTab::class)
            ->call('toggle', 'payment')
            ->assertHasNoErrors();

        $this->assertEquals('0', SystemSetting::get('modules', 'payment'));
    }

    public function test_non_super_admin_cannot_access_module_management(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ModulesTab::class)
            ->assertStatus(403);
    }

    public function test_deactivated_module_permission_is_denied_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::CASHIER); // cashier normally has payment.view

        // Sanity: payment.view is granted before deactivation.
        $this->assertTrue($user->can('payment.view'));

        SystemSetting::set('modules', 'payment', '0');

        // Permission is now denied because the module is deactivated.
        $this->assertFalse($user->can('payment.view'));
    }

    public function test_deactivated_module_still_accessible_by_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        SystemSetting::set('modules', 'payment', '0');

        $this->assertTrue($user->can('payment.view'));
    }

    public function test_settings_module_cannot_be_toggled(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ModulesTab::class)
            ->call('toggle', 'settings')
            ->assertHasNoErrors();

        // 'settings' is not in the toggleable surface, so no setting is written.
        $this->assertNull(SystemSetting::get('modules', 'settings'));
    }

    public function test_other_modules_are_unaffected_when_one_is_deactivated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::CASHIER);

        SystemSetting::set('modules', 'payment', '0');

        // Cashier still has dashboard access (unaffected module).
        $this->assertTrue($user->can('dashboard.view'));
    }

    public function test_deactivated_module_is_hidden_from_sidebar_for_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        SystemSetting::set('modules', 'payment', '0');

        $this->actingAs($user)
            ->get(route('dashboard.executive'))
            ->assertOk()
            ->assertDontSee('Payments')
            ->assertSee('System Settings');
    }

    public function test_deactivated_module_is_hidden_from_sidebar_for_regular_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::CASHIER);

        SystemSetting::set('modules', 'payment', '0');

        $this->actingAs($user)
            ->get(route('dashboard.budget'))
            ->assertOk()
            ->assertDontSee('Payments');
    }

    public function test_nav_group_is_hidden_when_all_modules_in_group_are_deactivated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        SystemSetting::set('modules', 'purchase-order', '0');
        SystemSetting::set('modules', 'payment', '0');

        $this->actingAs($user)
            ->get(route('dashboard.executive'))
            ->assertOk()
            ->assertDontSee('Award & Delivery')
            ->assertDontSee('Purchase Orders')
            ->assertDontSee('Payments');
    }

    public function test_sidebar_updates_in_realtime_when_module_is_deactivated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Layout\AppSidebar::class)
            ->assertSee('Payments')
            ->dispatch('module-toggled', module: 'payment', active: false)
            ->assertDontSee('Payments');
    }

    public function test_sidebar_updates_in_realtime_when_module_is_reactivated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        SystemSetting::set('modules', 'payment', '0');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Layout\AppSidebar::class)
            ->assertDontSee('Payments')
            ->dispatch('module-toggled', module: 'payment', active: true)
            ->assertSee('Payments');
    }

    public function test_sidebar_updates_in_realtime_when_all_modules_are_activated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        SystemSetting::set('modules', 'payment', '0');
        SystemSetting::set('modules', 'purchase-order', '0');

        $modules = collect(\App\Support\ModuleRegistry::toggleableKeys())
            ->mapWithKeys(fn (string $key) => [$key => true])
            ->all();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Layout\AppSidebar::class)
            ->assertDontSee('Payments')
            ->dispatch('modules-synced', modules: $modules)
            ->assertSee('Payments');
    }
}
