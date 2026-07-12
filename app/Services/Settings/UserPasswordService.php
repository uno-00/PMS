<?php

namespace App\Services\Settings;

use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserPasswordService
{
    public function generatePlainPassword(): string
    {
        return PasswordPolicy::generate();
    }

    public function reset(User $user, string $plainPassword): void
    {
        $user->forceFill([
            'password' => Hash::make($plainPassword),
            'must_change_password' => true,
            'password_changed_at' => null,
        ])->save();

        $this->invalidateSessions($user);
    }

    protected function invalidateSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->delete();
    }
}
