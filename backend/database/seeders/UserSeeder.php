<?php

namespace Database\Seeders;

use App\Models\Settings\Division;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One ready-to-use test account per role. All accounts share the password
 * below for local demo/testing purposes only; must_change_password forces
 * a reset on first real deployment. See docs/TEST_ACCOUNTS.md.
 */
class UserSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'Passw0rd!2026';

    public function run(): void
    {
        $itsDev = Division::query()->where('code', 'ITS-DEV')->first();
        $budget = Division::query()->where('code', 'FMS-BUD')->first();
        $planning = Division::query()->where('code', 'OSEC-PLN')->first();
        $bacSecretariat = Division::query()->where('code', 'GSS-BAC')->first();
        $supply = Division::query()->where('code', 'GSS-SUP')->first();
        $accounting = Division::query()->where('code', 'FMS-ACC')->first();
        $cash = Division::query()->where('code', 'FMS-CSH')->first();

        $accounts = [
            ['role' => Roles::SUPER_ADMIN, 'email' => 'superadmin@pms.gov.ph', 'name' => 'Sofia Reyes'],
            ['role' => Roles::SYSTEM_ADMIN, 'email' => 'sysadmin@pms.gov.ph', 'name' => 'Marco Villanueva'],
            ['role' => Roles::BAC_CHAIRPERSON, 'email' => 'bac.chair@pms.gov.ph', 'name' => 'Atty. Maria Santos', 'division' => $bacSecretariat],
            ['role' => Roles::BAC_SECRETARIAT, 'email' => 'bac.secretariat@pms.gov.ph', 'name' => 'Carlos Bautista', 'division' => $bacSecretariat],
            ['role' => Roles::BAC_MEMBER, 'email' => 'bac.member@pms.gov.ph', 'name' => 'Angela Cruz', 'division' => $bacSecretariat],
            ['role' => Roles::BUDGET_OFFICER, 'email' => 'budget.officer@pms.gov.ph', 'name' => 'Ramon Garcia', 'division' => $budget],
            ['role' => Roles::PLANNING_OFFICER, 'email' => 'planning.officer@pms.gov.ph', 'name' => 'Liza Fernandez', 'division' => $planning],
            ['role' => Roles::ACCOUNTING_OFFICER, 'email' => 'accounting.officer@pms.gov.ph', 'name' => 'Noel Ramos', 'division' => $accounting],
            ['role' => Roles::SUPPLY_OFFICER, 'email' => 'supply.officer@pms.gov.ph', 'name' => 'Grace Tolentino', 'division' => $supply],
            ['role' => Roles::DIVISION_CHIEF, 'email' => 'division.chief@pms.gov.ph', 'name' => 'Eduardo Lim', 'division' => $itsDev],
            ['role' => Roles::END_USER, 'email' => 'end.user@pms.gov.ph', 'name' => 'Patricia Ocampo', 'division' => $itsDev],
            ['role' => Roles::HOPE, 'email' => 'hope@pms.gov.ph', 'name' => 'Hon. Juan Dela Cruz'],
            ['role' => Roles::INTERNAL_AUDITOR, 'email' => 'auditor@pms.gov.ph', 'name' => 'Estrella Navarro'],
            ['role' => Roles::INSPECTOR, 'email' => 'inspector@pms.gov.ph', 'name' => 'Roberto Aquino', 'division' => $supply],
            ['role' => Roles::PROPERTY_OFFICER, 'email' => 'property.officer@pms.gov.ph', 'name' => 'Cecilia Mendoza', 'division' => $supply],
            ['role' => Roles::CASHIER, 'email' => 'cashier@pms.gov.ph', 'name' => 'Jonathan Perez', 'division' => $cash],
            ['role' => Roles::FINANCE, 'email' => 'finance@pms.gov.ph', 'name' => 'Katrina Del Rosario', 'division' => $accounting],
            ['role' => Roles::VIEWER, 'email' => 'viewer@pms.gov.ph', 'name' => 'Observer Account'],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'division_id' => $account['division']->id ?? null,
                    'is_active' => true,
                    'must_change_password' => false,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($account['role'])) {
                $user->assignRole($account['role']);
            }
        }

        $this->command?->info('Test accounts seeded ('.count($accounts).'). Default password: '.self::DEFAULT_PASSWORD);
    }
}
