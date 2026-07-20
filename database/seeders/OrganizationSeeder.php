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
        $brhmc = [
            'name' => 'Bicol Regional Hospital and Medical Center',
            'acronym' => 'BRHMC',
            'agency_code' => 'BRHMC-01',
            'address' => 'Rizal Avenue corner Bagtang Road, Barangay Sagpon, Daraga, Albay, Philippines',
            'region' => 'Region V',
            'tin' => '000-000-000-000',
            'head_of_agency' => 'Dr. Juan Dela Cruz',
            'hope_position' => 'Hospital Chief / Medical Center Chief II',
            'bac_chairperson' => 'Atty. Maria Santos',
            'contact_email' => 'procurement@brhmc.doh.gov.ph',
            'contact_phone' => '(052) 742-0000',
            'philgeps_organization_id' => 'BRHMC-0001',
        ];

        AgencyProfile::query()
            ->where('acronym', 'DSGS')
            ->orWhere('agency_code', '01-001')
            ->orWhere('name', 'Department of Sample Government Services')
            ->update($brhmc);

        AgencyProfile::query()->updateOrCreate(['agency_code' => 'BRHMC-01'], $brhmc);

        AgencyProfile::resetCached();

        $departments = [
            ['code' => 'OSEC', 'name' => 'Office of the Medical Center Chief'],
            ['code' => 'FMS', 'name' => 'Finance and Management Service'],
            ['code' => 'ITS', 'name' => 'Health Information Management Service'],
            ['code' => 'GSS', 'name' => 'General Services'],
        ];

        foreach ($departments as $dept) {
            $department = Department::query()->firstOrCreate(['code' => $dept['code']], $dept);

            $divisions = match ($dept['code']) {
                'OSEC' => [
                    ['code' => 'OSEC-PLN', 'name' => 'Planning and Quality Management (Implementing Unit)'],
                    ['code' => 'OSEC-LEG', 'name' => 'Legal Division'],
                ],
                'FMS' => [
                    ['code' => 'FMS-BUD', 'name' => 'Budget Division (Implementing Unit)'],
                    ['code' => 'FMS-ACC', 'name' => 'Accounting Division'],
                    ['code' => 'FMS-CSH', 'name' => 'Cash Division'],
                ],
                'ITS' => [
                    ['code' => 'ITS-DEV', 'name' => 'HMIS / Systems Development (Implementing Unit)'],
                    ['code' => 'ITS-NET', 'name' => 'Network & Infrastructure Division'],
                ],
                'GSS' => [
                    ['code' => 'GSS-SUP', 'name' => 'Supply and Pharmacy (Implementing Unit)'],
                    ['code' => 'GSS-BAC', 'name' => 'BAC Secretariat Division'],
                ],
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

        $this->command?->info('BRHMC organizational structure seeded: '.Department::count().' departments, '.Division::count().' divisions.');
    }
}
