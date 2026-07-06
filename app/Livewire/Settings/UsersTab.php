<?php

namespace App\Livewire\Settings;

use App\Models\Settings\Division;
use App\Models\User;
use App\Support\PasswordPolicy;
use App\Support\Roles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UsersTab extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public bool $showModal = false;

    public ?string $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role_name = '';

    public string $division_id = '';

    public bool $is_active = true;

    protected $queryString = ['search', 'roleFilter'];

    public function mount(): void
    {
        Gate::authorize('users.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
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

    protected function resetForm(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'role_name', 'division_id']);
        $this->is_active = true;
    }

    public function render()
    {
        $users = User::query()
            ->with(['division', 'roles'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter !== '', fn ($q) => $q->role($this->roleFilter))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.settings.users-tab', [
            'users' => $users,
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'divisions' => Division::query()->orderBy('name')->get(),
        ]);
    }
}
