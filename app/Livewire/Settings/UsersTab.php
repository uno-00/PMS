<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Settings\Division;
use App\Models\User;
use App\Services\Settings\UserPasswordService;
use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UsersTab extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterName = '';

    #[Url]
    public string $filterEmail = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $filterDivisionId = '';

    #[Url]
    public string $filterStatus = '';

    public bool $showModal = false;

    public ?string $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role_name = '';

    public string $division_id = '';

    public bool $is_active = true;

    public bool $showResetModal = false;

    public ?string $resetUserId = null;

    public string $resetUserName = '';

    public string $resetUserEmail = '';

    public string $resetPassword = '';

    public string $resetPasswordConfirmation = '';

    /** Shown once after a successful reset so the admin can copy it. */
    public ?string $resetPasswordResult = null;

    public function mount(): void
    {
        Gate::authorize('users.view');
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterName',
            'filterEmail',
            'roleFilter',
            'filterDivisionId',
            'filterStatus',
        ]);
    }

    public function openCreate(): void
    {
        Gate::authorize('users.create');
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $userId): void
    {
        Gate::authorize('users.edit');

        $user = User::query()->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role_name = $user->primaryRoleName() ?? '';
        $this->division_id = $user->division_id ?? '';
        $this->is_active = (bool) $user->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $isCreate = $this->editingUserId === null;

        Gate::authorize($isCreate ? 'users.create' : 'users.edit');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role_name' => ['required', 'string'],
            'division_id' => ['nullable', 'uuid', 'exists:divisions,id'],
            'is_active' => ['boolean'],
        ];

        if ($isCreate) {
            $rules['email'][] = 'unique:users,email';
            $rules['password'] = ['required', PasswordPolicy::rule()];
        } else {
            $rules['email'][] = 'unique:users,email,'.$this->editingUserId;
            if ($this->password !== '') {
                $rules['password'] = [PasswordPolicy::rule()];
            }
        }

        $this->validate($rules);

        if ($isCreate) {
            $user = User::query()->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'division_id' => $this->division_id ?: null,
                'is_active' => $this->is_active,
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);
        } else {
            $user = User::query()->findOrFail($this->editingUserId);

            if ($user->id === Auth::id() && ! $this->is_active) {
                $this->addError('is_active', 'You cannot deactivate your own account.');

                return;
            }

            $user->fill([
                'name' => $this->name,
                'email' => $this->email,
                'division_id' => $this->division_id ?: null,
                'is_active' => $this->is_active,
            ]);

            if ($this->password !== '') {
                $user->password = Hash::make($this->password);
                $user->must_change_password = true;
            }

            $user->save();
        }

        if (Gate::allows('users.manage-roles')) {
            $user->syncRoles([$this->role_name]);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('status', $isCreate ? 'User account created.' : 'User account updated.');
    }

    public function toggleActive(string $userId): void
    {
        Gate::authorize('users.edit');

        $user = User::query()->findOrFail($userId);

        if ($user->id === Auth::id()) {
            session()->flash('status', 'You cannot deactivate your own account.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);

        session()->flash('status', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function openResetPassword(string $userId): void
    {
        Gate::authorize('users.reset-password');

        $user = User::query()->findOrFail($userId);

        $this->resetUserId = $user->id;
        $this->resetUserName = $user->name;
        $this->resetUserEmail = $user->email;
        $this->resetPassword = '';
        $this->resetPasswordConfirmation = '';
        $this->resetPasswordResult = null;
        $this->resetErrorBag('resetPassword', 'resetPasswordConfirmation');
        $this->showResetModal = true;
    }

    public function generateResetPassword(UserPasswordService $service): void
    {
        Gate::authorize('users.reset-password');

        $generated = $service->generatePlainPassword();

        if ($this->showResetModal) {
            $this->resetPassword = $generated;
            $this->resetPasswordConfirmation = $generated;
            $this->resetPasswordResult = null;
        } else {
            $this->password = $generated;
        }
    }

    public function applyResetPassword(UserPasswordService $service): void
    {
        Gate::authorize('users.reset-password');

        $this->validate([
            'resetPassword' => ['required', PasswordPolicy::rule()],
            'resetPasswordConfirmation' => ['required', 'same:resetPassword'],
        ], [], [
            'resetPassword' => 'password',
            'resetPasswordConfirmation' => 'password confirmation',
        ]);

        $user = User::query()->findOrFail($this->resetUserId);

        $plain = $this->resetPassword;
        $service->reset($user, $plain);

        $this->resetPassword = '';
        $this->resetPasswordConfirmation = '';
        $this->resetPasswordResult = $plain;

        session()->flash(
            'status',
            "Password reset for {$user->name}. The user must change it on next login."
        );
    }

    public function closeResetPasswordModal(): void
    {
        $this->showResetModal = false;
        $this->reset([
            'resetUserId',
            'resetUserName',
            'resetUserEmail',
            'resetPassword',
            'resetPasswordConfirmation',
            'resetPasswordResult',
        ]);
    }

    protected function resetForm(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'role_name', 'division_id']);
        $this->is_active = true;
    }

    public function render()
    {
        $query = User::query()
            ->with(['division', 'roles']);

        $this->applyLikeFilter($query, 'name', $this->filterName);
        $this->applyLikeFilter($query, 'email', $this->filterEmail);

        if ($this->roleFilter !== '') {
            $query->role($this->roleFilter);
        }

        $this->applyExactFilter($query, 'division_id', $this->filterDivisionId);

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus === 'active');
        }

        $this->applyCreatedAtFilter($query);

        $users = $query->orderBy('name')->paginate(12);

        return view('livewire.settings.users-tab', [
            'users' => $users,
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'divisions' => Division::query()->orderBy('name')->get(),
        ]);
    }
}
