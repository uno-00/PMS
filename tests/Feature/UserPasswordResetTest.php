<?php

namespace Tests\Feature;

use App\Livewire\Settings\UsersTab;
use App\Models\User;
use App\Support\PasswordPolicy;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_super_admin_can_reset_user_password_with_generated_value(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);

        $target = User::factory()->create([
            'password' => Hash::make('OldPassw0rd!2026'),
            'must_change_password' => false,
        ]);

        $component = Livewire::actingAs($superAdmin)
            ->test(UsersTab::class)
            ->call('openResetPassword', $target->id)
            ->call('generateResetPassword')
            ->call('applyResetPassword')
            ->assertHasNoErrors();

        $generated = $component->get('resetPasswordResult');
        $this->assertIsString($generated);
        $this->assertNotSame('', $generated);

        $target->refresh();

        $this->assertTrue($target->must_change_password);
        $this->assertTrue(Hash::check($generated, $target->password));
    }

    public function test_super_admin_reset_password_updates_hash_and_forces_change(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);

        $target = User::factory()->create([
            'password' => Hash::make('OldPassw0rd!2026'),
            'must_change_password' => false,
        ]);

        $newPassword = PasswordPolicy::generate();

        Livewire::actingAs($superAdmin)
            ->test(UsersTab::class)
            ->call('openResetPassword', $target->id)
            ->set('resetPassword', $newPassword)
            ->set('resetPasswordConfirmation', $newPassword)
            ->call('applyResetPassword')
            ->assertHasNoErrors();

        $target->refresh();

        $this->assertTrue(Hash::check($newPassword, $target->password));
        $this->assertTrue($target->must_change_password);
        $this->assertFalse(Hash::check('OldPassw0rd!2026', $target->password));
    }

    public function test_system_admin_cannot_reset_user_password(): void
    {
        $systemAdmin = User::factory()->create();
        $systemAdmin->assignRole(Roles::SYSTEM_ADMIN);

        $target = User::factory()->create();

        Livewire::actingAs($systemAdmin)
            ->test(UsersTab::class)
            ->call('openResetPassword', $target->id)
            ->assertForbidden();
    }

    public function test_super_admin_can_generate_password_in_edit_form(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);

        $target = User::factory()->create();

        Livewire::actingAs($superAdmin)
            ->test(UsersTab::class)
            ->call('openEdit', $target->id)
            ->call('generateResetPassword')
            ->assertSet('password', fn ($value) => is_string($value) && strlen($value) >= 12);
    }
}
