<?php

namespace App\Support;

/**
 * Canonical role names (Spatie Permission "web" guard).
 * Keeping these as constants prevents typo-driven authorization bugs
 * across policies, gates, seeders, and Blade/Livewire checks.
 */
final class Roles
{
    public const SUPER_ADMIN = 'Super Admin';

    public const SYSTEM_ADMIN = 'System Admin';

    public const BAC_CHAIRPERSON = 'BAC Chairperson';

    public const BAC_SECRETARIAT = 'BAC Secretariat';

    public const BAC_MEMBER = 'BAC Member';

    public const BUDGET_OFFICER = 'Budget Officer';

    public const PLANNING_OFFICER = 'Planning Officer';

    public const ACCOUNTING_OFFICER = 'Accounting Officer';

    public const SUPPLY_OFFICER = 'Supply Officer';

    public const DIVISION_CHIEF = 'Division Chief';

    public const END_USER = 'End User';

    public const HOPE = 'HOPE';

    public const INTERNAL_AUDITOR = 'Internal Auditor';

    public const INSPECTOR = 'Inspector';

    public const PROPERTY_OFFICER = 'Property Officer';

    public const CASHIER = 'Cashier';

    public const FINANCE = 'Finance';

    public const BIDDER = 'Bidder';

    public const VIEWER = 'Viewer';

    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::SYSTEM_ADMIN,
            self::BAC_CHAIRPERSON,
            self::BAC_SECRETARIAT,
            self::BAC_MEMBER,
            self::BUDGET_OFFICER,
            self::PLANNING_OFFICER,
            self::ACCOUNTING_OFFICER,
            self::SUPPLY_OFFICER,
            self::DIVISION_CHIEF,
            self::END_USER,
            self::HOPE,
            self::INTERNAL_AUDITOR,
            self::INSPECTOR,
            self::PROPERTY_OFFICER,
            self::CASHIER,
            self::FINANCE,
            self::BIDDER,
            self::VIEWER,
        ];
    }

    /** Roles that belong to the internal agency portal (everything except Bidder). */
    public static function internal(): array
    {
        return array_values(array_diff(self::all(), [self::BIDDER]));
    }

    /** Roles that sit on the Bids and Awards Committee. */
    public static function bac(): array
    {
        return [self::BAC_CHAIRPERSON, self::BAC_SECRETARIAT, self::BAC_MEMBER];
    }
}
