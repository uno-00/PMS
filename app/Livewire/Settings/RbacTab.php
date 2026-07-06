<?php

namespace App\Livewire\Settings;

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacTab extends Component
{
    public string $selectedRole = '';

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function mount(): void
    {
        Gate::authorize('users.manage-roles');

        $this->selectedRole = Role::query()->where('guard_name', 'web')->orderBy('name')->value('name') ?? Roles::SUPER_ADMIN;
        $this->loadRolePermissions();
    }

    public function selectRole(string $roleName): void
    {
        Gate::authorize('users.manage-roles');
        $this->selectedRole = $roleName;
        $this->loadRolePermissions();
    }

    public function togglePermission(string $permission): void
    {
        Gate::authorize('users.manage-roles');

        if ($this->selectedRole === Roles::SUPER_ADMIN) {
            return;
        }

        if (in_array($permission, $this->selectedPermissions, true)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permission]));
        } else {
            $this->selectedPermissions[] = $permission;
        }
    }

    public function toggleModule(string $module): void
    {
        Gate::authorize('users.manage-roles');

        if ($this->selectedRole === Roles::SUPER_ADMIN) {
            return;
        }

        $modulePermissions = Permissions::forModule($module);
        $allSelected = empty(array_diff($modulePermissions, $this->selectedPermissions));

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $modulePermissions));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $modulePermissions)));
        }
    }

    public function save(): void
    {
        Gate::authorize('users.manage-roles');

        if ($this->selectedRole === Roles::SUPER_ADMIN) {
            session()->flash('status', 'Super Admin always has full access and cannot be restricted.');

            return;
        }

        $role = Role::query()->where('name', $this->selectedRole)->where('guard_name', 'web')->firstOrFail();

        $valid = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $this->selectedPermissions)
            ->pluck('name')
            ->all();

        $role->syncPermissions($valid);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        session()->flash('status', "Permissions updated for {$this->selectedRole}.");
        $this->loadRolePermissions();
    }

    protected function loadRolePermissions(): void
    {
        $role = Role::query()->where('name', $this->selectedRole)->where('guard_name', 'web')->first();

        $this->selectedPermissions = $role
            ? $role->permissions()->pluck('name')->all()
            : [];
    }

    public function render()
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        $matrix = Permissions::matrix();

        return view('livewire.settings.rbac-tab', compact('roles', 'matrix'));
    }
}
