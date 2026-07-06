<?php

namespace App\Livewire\Auth;

use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ForcePasswordChange extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function update(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
        ]);

        $user = Auth::user();
        $user->forceFill([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ])->save();

        $this->redirect(route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.force-password-change');
    }
}
