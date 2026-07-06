<?php

namespace Database\Seeders;

use App\Models\Settings\AgencyProfile;
use App\Models\Settings\CostCenter;
use App\Models\Settings\Department;
use App\Models\Settings\Division;
use App\Models\Settings\Office;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        AgencyProfile::query()->firstOrCreate(['name' => 'Department of Sample Government Services'], [
            'acronym' => 'DSGS',
            'agency_code' => '01-001',
            'address' => 'Government Center, Quezon City, Metro Manila',
            'region' => 'NCR',
            'tin' => '000-000-000-000',
            'head_of_agency' => 'Hon. Juan Dela Cruz',
            'hope_position' => 'Secretary',
            'bac_chairperson' => 'Atty. Maria Santos',
            'contact_email' => 'info@dsgs.gov.ph',
            'contact_phone' => '(02) 8888-0000',
            'philgeps_organization_id' => 'DSGS-0001',
        ]);

        $departments = [
            ['code' => 'OSEC', 'name' => 'Office of the Secretary'],
            ['code' => 'FMS', 'name' => 'Finance and Management Service'],
            ['code' => 'ITS', 'name' => 'Information Technology Service'],
            ['code' => 'GSS', 'name' => 'General Services'],
        ];

        foreach ($departments as $dept) {
            $department = Department::query()->firstOrCreate(['code' => $dept['code']], $dept);

            $divisions = match ($dept['code']) {
                'OSEC' => [['code' => 'OSEC-PLN', 'name' => 'Planning Division'], ['code' => 'OSEC-LEG', 'name' => 'Legal Division']],
                'FMS' => [['code' => 'FMS-BUD', 'name' => 'Budget Division'], ['code' => 'FMS-ACC', 'name' => 'Accounting Division'], ['code' => 'FMS-CSH', 'name' => 'Cash Division']],
                'ITS' => [['code' => 'ITS-DEV', 'name' => 'Systems Development Division'], ['code' => 'ITS-NET', 'name' => 'Network & Infrastructure Division']],
                'GSS' => [['code' => 'GSS-SUP', 'name' => 'Supply and Property Division'], ['code' => 'GSS-BAC', 'name' => 'BAC Secretariat Division']],
                default => [],
            };

            foreach ($divisions as $div) {
                $division = Division::query()->firstOrCreate(['code' => $div['code']], array_merge($div, ['department_id' => $department->id]));

                Office::query()->firstOrCreate(['code' => $div['code'].'-MAIN'], [
                    'division_id' => $division->id,
                    'name' => $div['name'].' - Main Office',
                ]);

                CostCenter::query()->firstOrCreate(['code' => $div['code'].'-CC'], [
                    'division_id' => $division->id,
                    'name' => $div['name'].' Cost Center',
                ]);
            }
        }

        $this->command?->info('Organizational structure seeded: '.Department::count().' departments, '.Division::count().' divisions.');
    }
}
