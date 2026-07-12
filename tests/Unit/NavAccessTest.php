<?php

namespace Tests\Unit;

use App\Models\Settings\SystemSetting;
use App\Support\NavAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_nav_hides_deactivated_module_even_for_super_admin(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole(\App\Support\Roles::SUPER_ADMIN);

        SystemSetting::set('modules', 'payment', '0');

        $this->assertTrue($user->can('payment.view'));
        $this->assertFalse(NavAccess::can('payment.view', $user));
    }

    public function test_nav_shows_active_module_when_user_has_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole(\App\Support\Roles::CASHIER);

        $this->assertTrue(NavAccess::can('payment.view', $user));
    }
}
