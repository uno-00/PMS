<?php

namespace Database\Seeders;

use App\Enums\AnnualProcurementPlanStatus;
use App\Enums\GaaStatus;
use App\Enums\PpmpStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\Planning\Ppmp;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\Settings\FundSource;
use App\Models\Settings\ModeOfProcurement;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use App\Models\User;
use App\Services\Budget\BudgetAllocationService;
use App\Services\Procurement\CafService;
use Illuminate\Database\Seeder;

/**
 * End-to-end sample data walking through the full GAA -> APP -> Budget
 * Allocation -> PPMP -> Purchase Request -> CAF pipeline for FY 2026, so
 * a fresh install has something meaningful to explore immediately after
 * `php artisan migrate --seed`.
 */
class SampleProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@pms.gov.ph')->first();
        $budgetOfficer = User::query()->where('email', 'budget.officer@pms.gov.ph')->first();
        $itsDivision = Division::query()->where('code', 'ITS-DEV')->first();
        $fundSource = FundSource::query()->where('code', '101')->first();
        $uacs = UacsCode::query()->where('code', '5060402000')->first();
        $pap = Pap::query()->where('type', 'activity')->first();

        if (! $itsDivision || ! $fundSource || ! $uacs || ! $pap) {
            $this->command?->warn('Skipping SampleProcurementSeeder: run OrganizationSeeder and ReferenceDataSeeder first.');

            return;
        }

        $fiscalYear = FiscalYear::query()->firstOrCreate(['year' => 2026], [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_current' => true,
            'created_by' => $superAdmin?->id,
        ]);

        $gaa = GeneralAppropriationsAct::query()->firstOrCreate(
            ['fiscal_year_id' => $fiscalYear->id],
            [
                'title' => 'General Appropriations Act FY 2026',
                'reference_no' => 'RA-12009-2026',
                'total_amount' => 5000000,
                'status' => GaaStatus::Draft,
                'uploaded_by' => $budgetOfficer?->id,
            ]
        );

        if ($gaa->lineItems()->count() === 0) {
            $lineItems = [
                ['division_code' => 'ITS-DEV', 'amount' => 5_000_000, 'description' => 'ICT Equipment and Systems Development'],
                ['division_code' => 'ITS-NET', 'amount' => 3_000_000, 'description' => 'Network Infrastructure and Data Center'],
                ['division_code' => 'GSS-SUP', 'amount' => 2_500_000, 'description' => 'General Supplies and Property Management'],
                ['division_code' => 'FMS-BUD', 'amount' => 2_000_000, 'description' => 'Budget and Financial Systems'],
                ['division_code' => 'OSEC-PLN', 'amount' => 3_500_000, 'description' => 'Planning and Museum Programs'],
            ];

            $lineNo = 1;
            $totalAmount = 0;

            foreach ($lineItems as $row) {
                $division = Division::query()->where('code', $row['division_code'])->first();
                if (! $division) {
                    continue;
                }

                $gaa->lineItems()->create([
                    'line_no' => $lineNo++,
                    'department_id' => $division->department_id,
                    'division_id' => $division->id,
                    'pap_id' => $pap->id,
                    'uacs_code_id' => $uacs->id,
                    'fund_source_id' => $fundSource->id,
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                ]);

                $totalAmount += $row['amount'];
            }

            $gaa->update(['total_amount' => $totalAmount]);
        }

        if ($gaa->status === GaaStatus::Draft) {
            $gaa->transitionTo(GaaStatus::Validated, 'Sample data validated.', enforce: false);
            $gaa->update(['approved_by' => $budgetOfficer?->id, 'approved_at' => now()]);
            $gaa->transitionTo(GaaStatus::Approved, 'Sample data approved.', enforce: false);
            $fiscalYear->update(['total_gaa_amount' => $gaa->total_amount]);

            app(BudgetAllocationService::class)->seedFromGaa($gaa);
            $gaa->update(['distributed_by' => $budgetOfficer?->id, 'distributed_at' => now()]);
            $gaa->transitionTo(GaaStatus::Distributed, 'Sample data distributed.', enforce: false);
        }

        $app = AnnualProcurementPlan::query()->firstOrCreate(
            ['fiscal_year_id' => $fiscalYear->id],
            [
                'gaa_id' => $gaa->id,
                'reference_no' => 'APP-2026',
                'total_budget' => $gaa->total_amount,
                'status' => AnnualProcurementPlanStatus::Approved,
                'approved_by' => $superAdmin?->id,
                'approved_at' => now(),
            ]
        );

        $rootAllocation = $gaa->refresh()->lineItems()->first()
            ? BudgetAllocation::query()->where('gaa_id', $gaa->id)->whereNull('parent_id')->first()
            : null;

        $ppmp = Ppmp::query()->firstOrCreate(
            ['fiscal_year_id' => $fiscalYear->id, 'division_id' => $itsDivision->id, 'ppmp_type' => 'regular'],
            [
                'annual_procurement_plan_id' => $app->id,
                'title' => 'ITS Systems Development Division PPMP FY 2026',
                'status' => PpmpStatus::Draft,
                'prepared_by' => $budgetOfficer?->id,
            ]
        );

        if ($ppmp->items()->count() === 0 && $rootAllocation) {
            $ppmp->items()->create([
                'item_no' => 1,
                'item_name' => 'Desktop Computers (Core i7, 16GB RAM)',
                'description' => 'Replacement units for the Systems Development Division',
                'unit' => 'unit',
                'quantity' => 20,
                'estimated_unit_cost' => 55000,
                'schedule_start' => '2026-03-01',
                'schedule_end' => '2026-04-30',
                'mode_of_procurement_id' => ModeOfProcurement::query()->where('code', 'SVP')->value('id'),
                'fund_source_id' => $fundSource->id,
                'pap_id' => $pap->id,
                'uacs_code_id' => $uacs->id,
                'budget_allocation_id' => $rootAllocation->id,
            ]);

            $ppmp->items()->create([
                'item_no' => 2,
                'item_name' => 'Network Switches (24-port managed)',
                'description' => 'Backbone network upgrade',
                'unit' => 'unit',
                'quantity' => 10,
                'estimated_unit_cost' => 45000,
                'schedule_start' => '2026-05-01',
                'schedule_end' => '2026-06-30',
                'mode_of_procurement_id' => ModeOfProcurement::query()->where('code', 'SVP')->value('id'),
                'fund_source_id' => $fundSource->id,
                'pap_id' => $pap->id,
                'uacs_code_id' => $uacs->id,
                'budget_allocation_id' => $rootAllocation->id,
            ]);

            if ($ppmp->status === PpmpStatus::Draft) {
                $ppmp->transitionTo(PpmpStatus::DivisionChiefReview, 'Sample data.', enforce: false);
                $ppmp->transitionTo(PpmpStatus::PlanningReview, 'Sample data.', enforce: false);
                $ppmp->transitionTo(PpmpStatus::BacConsolidation, 'Sample data.', enforce: false);
                $ppmp->transitionTo(PpmpStatus::ProcurementModeReview, 'Sample data.', enforce: false);
                $ppmp->transitionTo(PpmpStatus::BudgetValidation, 'Sample data.', enforce: false);
                $ppmp->transitionTo(PpmpStatus::Approved, 'Sample data budget validated.', enforce: false);

                foreach ($ppmp->items as $item) {
                    app(BudgetAllocationService::class)->utilize($item->budgetAllocation, (float) $item->abc);
                }

                $ppmp->update(['approved_by' => $superAdmin?->id, 'approved_at' => now()]);
                $app->recalculatePlannedAmount();
            }
        }

        $firstItem = $ppmp->items()->first();

        if ($firstItem && PurchaseRequest::query()->where('ppmp_id', $ppmp->id)->doesntExist()) {
            $pr = PurchaseRequest::query()->create([
                'fiscal_year_id' => $fiscalYear->id,
                'division_id' => $itsDivision->id,
                'ppmp_id' => $ppmp->id,
                'purpose' => 'Procurement of desktop computers for the Systems Development Division',
                'status' => PurchaseRequestStatus::Draft,
                'requested_by' => $budgetOfficer?->id,
            ]);

            $pr->items()->create([
                'ppmp_item_id' => $firstItem->id,
                'item_name' => $firstItem->item_name,
                'description' => $firstItem->description,
                'unit' => $firstItem->unit,
                'quantity' => $firstItem->quantity,
                'unit_cost' => $firstItem->estimated_unit_cost,
            ]);
            $pr->refresh();

            $pr->transitionTo(PurchaseRequestStatus::DivisionChief, 'Sample data.', enforce: false);
            $pr->transitionTo(PurchaseRequestStatus::Planning, 'Sample data.', enforce: false);
            $pr->transitionTo(PurchaseRequestStatus::Budget, 'Sample data.', enforce: false);
            $pr->update(['hope_by' => $superAdmin?->id, 'hope_at' => now()]);
            $pr->transitionTo(PurchaseRequestStatus::Approved, 'Sample data approved by HOPE.', enforce: false);

            app(CafService::class)->generate($pr, $budgetOfficer);
        }

        $this->command?->info('Sample FY 2026 procurement lifecycle seeded (GAA -> APP -> PPMP -> PR -> CAF).');
    }
}
