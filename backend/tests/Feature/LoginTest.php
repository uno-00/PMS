<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Portal\Login as PortalLogin;
use App\Models\User;
use App\Support\DemoAccounts;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Confirms the actual login flow (not just Auth::attempt in isolation)
 * works end-to-end for every seeded demo account, including the session
 * regeneration step that requires the request to be dispatched through
 * the 'web' middleware group (hence the `$this->get(...)` warm-up call
 * before each Livewire::test(), which is what wires up the session store
 * that Livewire component method calls resolve via request()->session()).
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_internal_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'agent.smoke@pms.gov.ph',
            'password' => Hash::make(DemoAccounts::PASSWORD),
            'is_active' => true,
        ]);
        $user->assignRole(Roles::VIEWER);

        $this->get(route('login'));

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', DemoAccounts::PASSWORD)
            ->call('login')
            ->assertRedirect(route('dashboard', [], false))
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_internal_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'agent.smoke2@pms.gov.ph',
            'password' => Hash::make(DemoAccounts::PASSWORD),
            'is_active' => true,
        ]);
        $user->assignRole(Roles::VIEWER);

        $this->get(route('login'));

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_every_seeded_demo_account_can_login(): void
    {
        foreach (DemoAccounts::internal() as $account) {
            $user = User::where('email', $account['email'])->firstOrFail();

            $this->get(route('login'));

            Livewire::test(Login::class)
                ->set('email', $account['email'])
                ->set('password', DemoAccounts::PASSWORD)
                ->call('login')
                ->assertRedirect()
                ->assertHasNoErrors();

            $this->assertAuthenticatedAs($user->fresh());

            auth()->logout();
        }
    }

    public function test_demo_account_one_click_login(): void
    {
        $account = DemoAccounts::internal()[0];
        $user = User::where('email', $account['email'])->firstOrFail();

        $this->get(route('login'));

        Livewire::test(Login::class)
            ->call('loginAsDemo', $account['email'])
            ->assertRedirect(route('dashboard', [], false))
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_demo_account_fill_and_login_via_wire_click(): void
    {
        $this->seed();

        $account = DemoAccounts::internal()[0];
        $user = User::where('email', $account['email'])->firstOrFail();

        Livewire::test(Login::class)
            ->call('fillDemoAccount', $account['email'])
            ->assertSet('email', $account['email'])
            ->assertSet('password', DemoAccounts::PASSWORD)
            ->call('login')
            ->assertRedirect()
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_login_redirect_stays_on_current_host_in_local(): void
    {
        $user = User::where('email', 'superadmin@pms.gov.ph')->firstOrFail();

        $this->get('http://localhost:8001/login', ['HTTP_HOST' => 'localhost:8001']);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', DemoAccounts::PASSWORD)
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_bidder_can_login_via_portal(): void
    {
        $account = DemoAccounts::bidder()[0];

        $user = User::where('email', $account['email'])->firstOrFail();

        $this->get(route('bidder.login'));

        Livewire::test(PortalLogin::class)
            ->set('email', $account['email'])
            ->set('password', DemoAccounts::PASSWORD)
            ->call('login')
            ->assertRedirect()
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user->fresh());
    }
}
