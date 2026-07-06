<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            OrganizationSeeder::class,
            ReferenceDataSeeder::class,
            ApprovalRoutingSeeder::class,
            UserSeeder::class,
            BacMemberSeeder::class,
            BidderPortalSeeder::class,
            SampleProcurementSeeder::class,
        ]);
    }
}
