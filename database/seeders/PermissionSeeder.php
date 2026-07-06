<?php

namespace Database\Seeders;

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permissions::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Roles::all() as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            $permissions = config("rbac.permissions.{$roleName}", []);

            if ($permissions === ['*']) {
                $role->syncPermissions(Permission::where('guard_name', 'web')->get());

                continue;
            }

            $role->givePermissionTo(array_unique($permissions));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('Roles and permissions seeded: '.count(Roles::all()).' roles, '.count(Permissions::all()).' permissions.');
    }
}
