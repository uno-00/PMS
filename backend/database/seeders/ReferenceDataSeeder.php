<?php

namespace Database\Seeders;

use App\Models\Settings\FundSource;
use App\Models\Settings\ModeOfProcurement;
use App\Models\Settings\Pap;
use App\Models\Settings\ProcurementThreshold;
use App\Models\Settings\UacsCode;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFundSources();
        $this->seedUacsCodes();
        $this->seedPaps();
        $modes = $this->seedModesOfProcurement();
        $this->seedThresholds($modes);
    }

    protected function seedFundSources(): void
    {
        foreach ([
            ['code' => '101', 'name' => 'General Fund'],
            ['code' => '102', 'name' => 'Trust Fund'],
            ['code' => '103', 'name' => 'Special Education Fund'],
            ['code' => '104', 'name' => 'Foreign-Assisted Fund'],
        ] as $row) {
            FundSource::query()->firstOrCreate(['code' => $row['code']], $row);
        }
    }

    protected function seedUacsCodes(): void
    {
        foreach ([
            ['code' => '5020301000', 'description' => 'Office Supplies Expenses', 'expense_class' => 'MOOE'],
            ['code' => '5020399000', 'description' => 'Other Supplies and Materials Expenses', 'expense_class' => 'MOOE'],
            ['code' => '5020503000', 'description' => 'Internet Subscription Expenses', 'expense_class' => 'MOOE'],
            ['code' => '5021001000', 'description' => 'Repairs and Maintenance - Buildings', 'expense_class' => 'MOOE'],
            ['code' => '5060402000', 'description' => 'Information and Communications Technology Equipment', 'expense_class' => 'Capital Outlay'],
            ['code' => '5060405000', 'description' => 'Furniture and Fixtures', 'expense_class' => 'Capital Outlay'],
            ['code' => '5010101000', 'description' => 'Salaries and Wages - Regular', 'expense_class' => 'Personnel Services'],
        ] as $row) {
            UacsCode::query()->firstOrCreate(['code' => $row['code']], $row);
        }
    }

    protected function seedPaps(): void
    {
        $program = Pap::query()->firstOrCreate(
            ['code' => '310000000000000'],
            ['type' => 'program', 'name' => 'General Administration and Support Program']
        );

        foreach ([
            ['code' => '310100100001000', 'name' => 'General Management and Supervision'],
            ['code' => '310100100002000', 'name' => 'ICT Systems Operations and Maintenance'],
            ['code' => '310100100003000', 'name' => 'Procurement Management Services'],
        ] as $activity) {
            Pap::query()->firstOrCreate(
                ['code' => $activity['code']],
                ['type' => 'activity', 'parent_id' => $program->id, 'name' => $activity['name']]
            );
        }
    }

    protected function seedModesOfProcurement(): array
    {
        $modes = [
            ['code' => 'PB', 'name' => 'Public Bidding', 'requires_bac' => true, 'requires_philgeps_posting' => true, 'sort_order' => 1],
            ['code' => 'LSB', 'name' => 'Limited Source Bidding', 'requires_bac' => true, 'requires_philgeps_posting' => true, 'sort_order' => 2],
            ['code' => 'DC', 'name' => 'Direct Contracting', 'requires_bac' => true, 'requires_philgeps_posting' => true, 'sort_order' => 3],
            ['code' => 'RB', 'name' => 'Repeat Order', 'requires_bac' => false, 'requires_philgeps_posting' => false, 'sort_order' => 4],
            ['code' => 'SHOP', 'name' => 'Shopping', 'requires_bac' => false, 'requires_philgeps_posting' => false, 'sort_order' => 5],
            ['code' => 'SVP', 'name' => 'Small Value Procurement', 'requires_bac' => true, 'requires_philgeps_posting' => true, 'sort_order' => 6],
            ['code' => 'NP', 'name' => 'Negotiated Procurement', 'requires_bac' => true, 'requires_philgeps_posting' => true, 'sort_order' => 7],
        ];

        $created = [];
        foreach ($modes as $mode) {
            $created[$mode['code']] = ModeOfProcurement::query()->firstOrCreate(['code' => $mode['code']], $mode);
        }

        return $created;
    }

    protected function seedThresholds(array $modes): void
    {
        $today = now()->startOfYear()->toDateString();

        $thresholds = [
            ['mode' => 'SHOP', 'category' => 'goods', 'min' => 0, 'max' => 50000],
            ['mode' => 'SVP', 'category' => 'goods', 'min' => 50000.01, 'max' => 1000000],
            ['mode' => 'PB', 'category' => 'goods', 'min' => 1000000.01, 'max' => null],
            ['mode' => 'SHOP', 'category' => 'infrastructure', 'min' => 0, 'max' => 50000],
            ['mode' => 'SVP', 'category' => 'infrastructure', 'min' => 50000.01, 'max' => 1000000],
            ['mode' => 'PB', 'category' => 'infrastructure', 'min' => 1000000.01, 'max' => null],
            ['mode' => 'SHOP', 'category' => 'consulting_services', 'min' => 0, 'max' => 100000],
            ['mode' => 'SVP', 'category' => 'consulting_services', 'min' => 100000.01, 'max' => 1000000],
            ['mode' => 'PB', 'category' => 'consulting_services', 'min' => 1000000.01, 'max' => null],
        ];

        foreach ($thresholds as $t) {
            ProcurementThreshold::query()->firstOrCreate([
                'mode_of_procurement_id' => $modes[$t['mode']]->id,
                'category' => $t['category'],
            ], [
                'min_amount' => $t['min'],
                'max_amount' => $t['max'],
                'effective_date' => $today,
            ]);
        }
    }
}
