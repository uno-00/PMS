<?php

namespace App\Livewire\Portal;

use App\Support\DemoAccounts;
use App\Support\Roles;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal-guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    #[Computed]
    public function demoAccountsEnabled(): bool
    {
        return DemoAccounts::enabled();
    }

    #[Computed]
    public function demoAccounts(): array
    {
        return DemoAccounts::bidder();
    }

    public function fillDemoAccount(string $email): void
    {
        if (! DemoAccounts::enabled()) {
            return;
        }

        $allowed = collect(DemoAccounts::bidder())->pluck('email');

        if (! $allowed->contains($email)) {
            return;
        }

        $this->resetErrorBag();
        $this->email = $email;
        $this->password = DemoAccounts::PASSWORD;
        RateLimiter::clear($this->throttleKey());
    }

    public function loginAsDemo(string $email): void
    {
        $this->fillDemoAccount($email);

        if ($this->email === '' || $this->password === '') {
            return;
        }

        $this->login();
    }

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($this->isRateLimited()) {
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password, 'is_active' => true], $this->remember)) {
            RateLimiter::hit($this->throttleKey());
            $this->addError('email', 'These credentials do not match our records, or the account has been deactivated.');

            return;
        }

        if (! Auth::user()->hasRole(Roles::BIDDER)) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());
            $this->addError('email', 'This account is not registered as a Supplier/Bidder. Please use the staff login.');

            return;
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        Auth::user()->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->save();

        $this->redirect(route('bidder.dashboard', [], false), navigate: false);
    }

    protected function isRateLimited(): bool
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return false;
        }

        event(new Lockout(request()));
        $seconds = RateLimiter::availableIn($this->throttleKey());
        $this->addError('email', "Too many login attempts. Please try again in {$seconds} seconds.");

        return true;
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.portal.login');
    }
}
