<?php

namespace Database\Seeders;

use App\Models\Supplier\Bidder;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Additional verified bidders so competitive bidding screens show
 * multiple participants beyond the default portal demo account.
 */
class AdditionalBiddersSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'email' => 'bidder2@supplier.com',
                'name' => 'MetroTech Solutions Inc. (Authorized Rep.)',
                'company_name' => 'MetroTech Solutions Inc.',
                'philgeps_registration_no' => 'PG-2026-000456',
                'contact_person' => 'Ana Patricia Reyes',
                'phone' => '+63 918 555 0101',
                'address' => '88 Ayala Avenue, Makati City, Metro Manila',
            ],
            [
                'email' => 'bidder3@supplier.com',
                'name' => 'PrimeGov Supply Chain Corp. (Authorized Rep.)',
                'company_name' => 'PrimeGov Supply Chain Corp.',
                'philgeps_registration_no' => 'PG-2026-000789',
                'contact_person' => 'Roberto Mendoza',
                'phone' => '+63 919 555 0202',
                'address' => '15 Ortigas Center, Pasig City, Metro Manila',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make(BidderPortalSeeder::DEFAULT_PASSWORD),
                    'is_active' => true,
                    'must_change_password' => false,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole(Roles::BIDDER)) {
                $user->assignRole(Roles::BIDDER);
            }

            Bidder::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'user_id' => $user->id,
                    'company_name' => $account['company_name'],
                    'business_type' => 'corporation',
                    'philgeps_registration_no' => $account['philgeps_registration_no'],
                    'philgeps_registration_expiry' => now()->addYear(),
                    'mayor_permit_no' => 'MP-2026-'.substr(md5($account['email']), 0, 5),
                    'mayor_permit_expiry' => now()->addMonths(10),
                    'tax_clearance_no' => 'TC-2026-'.substr(md5($account['email']), 0, 5),
                    'tax_clearance_expiry' => now()->addMonths(8),
                    'sec_dti_registration_no' => 'CS2026'.substr(md5($account['email']), 0, 6),
                    'tin' => '999-888-777-'.str_pad((string) (crc32($account['email']) % 1000), 3, '0', STR_PAD_LEFT),
                    'contact_person' => $account['contact_person'],
                    'phone' => $account['phone'],
                    'address' => $account['address'],
                    'status' => 'verified',
                    'remarks' => 'Seeded demo bidder for competitive bidding scenarios.',
                ]
            );
        }

        $this->command?->info('Additional bidder accounts seeded: bidder2@supplier.com, bidder3@supplier.com');
    }
}
