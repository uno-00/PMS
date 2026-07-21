<?php

namespace Database\Seeders;

use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Ready-to-use Bidder Portal test account (Phase 9). Distinct from
 * UserSeeder because bidder accounts are not agency staff: they carry no
 * division/department and always link 1:1 to a Supplier\Bidder profile.
 */
class BidderPortalSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = UserSeeder::DEFAULT_PASSWORD;

    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'bidder@supplier.com'],
            [
                'name' => 'Juan Dela Cruz Trading Corp. (Authorized Rep.)',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'is_active' => true,
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole(Roles::BIDDER)) {
            $user->assignRole(Roles::BIDDER);
        }

        Bidder::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'Juan Dela Cruz Trading Corp.',
                'business_type' => 'corporation',
                'philgeps_registration_no' => 'PG-2026-000123',
                'philgeps_registration_expiry' => now()->addYear(),
                'mayor_permit_no' => 'MP-2026-04521',
                'mayor_permit_expiry' => now()->addMonths(10),
                'tax_clearance_no' => 'TC-2026-98213',
                'tax_clearance_expiry' => now()->addMonths(8),
                'sec_dti_registration_no' => 'CS202612345',
                'pcab_license_no' => null,
                'tin' => '123-456-789-000',
                'contact_person' => 'Juan Dela Cruz',
                'email' => 'bidder@supplier.com',
                'phone' => '+63 917 123 4567',
                'address' => '123 Rizal St., Makati City, Metro Manila',
                'status' => 'verified',
                'remarks' => 'Seeded demo bidder account, pre-verified by BAC Secretariat.',
            ]
        );

        $this->command?->info('Bidder Portal test account seeded: bidder@supplier.com / '.self::DEFAULT_PASSWORD);
    }
}
