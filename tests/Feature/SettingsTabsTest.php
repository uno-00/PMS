<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Roles;
use App\Support\ThemeSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsTabsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_super_admin_can_save_theme_colors(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\AppearanceTab::class)
            ->set('primary', '#166534')
            ->set('gradient_from', '#166534')
            ->set('gradient_via', '#14532d')
            ->set('gradient_to', '#052e16')
            ->call('save')
            ->assertHasNoErrors();

        $theme = ThemeSettings::all();
        $this->assertSame('#166534', $theme['primary']);
        $this->assertSame('#052e16', $theme['gradient_to']);
    }

    public function test_system_admin_can_list_users_tab(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SYSTEM_ADMIN);

        $this->actingAs($user)
            ->get(route('settings.index', ['tab' => 'users']))
            ->assertOk()
            ->assertSee('Manage agency user accounts');
    }

    public function test_system_admin_can_update_role_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::SYSTEM_ADMIN);

        $role = Role::query()->where('name', Roles::VIEWER)->firstOrFail();
        $permission = Permission::query()->where('name', 'users.create')->firstOrFail();
        $role->revokePermissionTo($permission);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\RbacTab::class)
            ->call('selectRole', Roles::VIEWER)
            ->call('togglePermission', $permission->name)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo($permission));
    }

    public function test_viewer_cannot_access_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertForbidden();
    }
}
