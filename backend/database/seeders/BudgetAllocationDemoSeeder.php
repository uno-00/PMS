<?php

namespace Database\Seeders;

use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\Settings\FundSource;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Ensures each demo division has a budget allocation pool for FY 2026 so
 * PPMP budget linkage dropdowns are populated during Budget Validation.
 */
class BudgetAllocationDemoSeeder extends Seeder
{
    /** @var array<string, array{amount: float, description: string}> */
    protected array $divisionPools = [
        'ITS-DEV' => ['amount' => 5_000_000, 'description' => 'ICT equipment and systems development'],
        'ITS-NET' => ['amount' => 3_000_000, 'description' => 'Network infrastructure and data center'],
        'GSS-SUP' => ['amount' => 2_500_000, 'description' => 'General supplies and property management'],
        'FMS-BUD' => ['amount' => 2_000_000, 'description' => 'Budget and financial systems'],
        'OSEC-PLN' => ['amount' => 3_500_000, 'description' => 'Planning and museum programs'],
    ];

    public function run(): void
    {
        $fiscalYear = FiscalYear::query()->where('year', 2026)->first();
        $fundSource = FundSource::query()->where('code', '101')->first();
        $uacs = UacsCode::query()->where('code', '5060402000')->first();
        $pap = Pap::query()->where('type', 'activity')->first();
        $budgetOfficer = User::query()->where('email', 'budget.officer@pms.gov.ph')->first();
        $gaa = $fiscalYear
            ? GeneralAppropriationsAct::query()->where('fiscal_year_id', $fiscalYear->id)->first()
            : null;

        if (! $fiscalYear || ! $fundSource || ! $uacs || ! $pap) {
            $this->command?->warn('Skipping BudgetAllocationDemoSeeder: run OrganizationSeeder and ReferenceDataSeeder first.');

            return;
        }

        $created = 0;

        foreach ($this->divisionPools as $divisionCode => $pool) {
            $division = Division::query()->where('code', $divisionCode)->first();
            if (! $division) {
                continue;
            }

            $allocation = BudgetAllocation::query()->firstOrCreate(
                [
                    'fiscal_year_id' => $fiscalYear->id,
                    'division_id' => $division->id,
                    'parent_id' => null,
                ],
                [
                    'gaa_id' => $gaa?->id,
                    'level' => 'department',
                    'department_id' => $division->department_id,
                    'pap_id' => $pap->id,
                    'fund_source_id' => $fundSource->id,
                    'uacs_code_id' => $uacs->id,
                    'allocated_amount' => $pool['amount'],
                    'utilized_amount' => 0,
                    'remarks' => 'Demo allocation — '.$pool['description'].'.',
                    'created_by' => $budgetOfficer?->id,
                ]
            );

            if ($allocation->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command?->info('Budget allocation demo pools ready ('.$created.' created, '.BudgetAllocation::query()->where('fiscal_year_id', $fiscalYear->id)->count().' total for FY 2026).');
    }
}
