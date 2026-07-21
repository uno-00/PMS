<?php

namespace App\Support;

use Database\Seeders\UserSeeder;

/**
 * Single source of truth for the seeded demo/test-account credentials
 * (see UserSeeder / BidderPortalSeeder) shown on the login screens for
 * UAT/demo convenience. Gated by config('app.demo_accounts_visible') so
 * it can never leak onto a real production login page.
 */
final class DemoAccounts
{
    public const PASSWORD = UserSeeder::DEFAULT_PASSWORD;

    public static function enabled(): bool
    {
        return (bool) config('app.demo_accounts_visible');
    }

    /** Internal agency portal accounts, one per role, grouped for a compact login-page list. */
    public static function internal(): array
    {
        return [
            ['role' => Roles::SUPER_ADMIN, 'email' => 'superadmin@pms.gov.ph'],
            ['role' => Roles::SYSTEM_ADMIN, 'email' => 'sysadmin@pms.gov.ph'],
            ['role' => Roles::BAC_CHAIRPERSON, 'email' => 'bac.chair@pms.gov.ph'],
            ['role' => Roles::BAC_SECRETARIAT, 'email' => 'bac.secretariat@pms.gov.ph'],
            ['role' => Roles::BAC_MEMBER, 'email' => 'bac.member@pms.gov.ph'],
            ['role' => Roles::BUDGET_OFFICER, 'email' => 'budget.officer@pms.gov.ph'],
            ['role' => Roles::PLANNING_OFFICER, 'email' => 'planning.officer@pms.gov.ph'],
            ['role' => Roles::ACCOUNTING_OFFICER, 'email' => 'accounting.officer@pms.gov.ph'],
            ['role' => Roles::SUPPLY_OFFICER, 'email' => 'supply.officer@pms.gov.ph'],
            ['role' => Roles::DIVISION_CHIEF, 'email' => 'division.chief@pms.gov.ph'],
            ['role' => Roles::END_USER, 'email' => 'end.user@pms.gov.ph'],
            ['role' => Roles::HOPE, 'email' => 'hope@pms.gov.ph'],
            ['role' => Roles::INTERNAL_AUDITOR, 'email' => 'auditor@pms.gov.ph'],
            ['role' => Roles::INSPECTOR, 'email' => 'inspector@pms.gov.ph'],
            ['role' => Roles::PROPERTY_OFFICER, 'email' => 'property.officer@pms.gov.ph'],
            ['role' => Roles::CASHIER, 'email' => 'cashier@pms.gov.ph'],
            ['role' => Roles::FINANCE, 'email' => 'finance@pms.gov.ph'],
            ['role' => Roles::VIEWER, 'email' => 'viewer@pms.gov.ph'],
        ];
    }

    /** Bidder Portal demo account(s). */
    public static function bidder(): array
    {
        return [
            ['role' => 'Bidder / Supplier', 'email' => 'bidder@supplier.com'],
        ];
    }
}
