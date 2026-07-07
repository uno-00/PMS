<?php

namespace Database\Seeders;

use App\Enums\MarketScopingConsidered;
use App\Enums\MarketScopingStatus;
use App\Models\Planning\MarketScoping;
use App\Models\Settings\AgencyProfile;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds sample Market Scoping Checklists aligned with the FY 2026 demo
 * procurement pipeline (ITS desktop computers and GSS office supplies).
 */
class MarketScopingSeeder extends Seeder
{
    public function run(): void
    {
        $agency = AgencyProfile::current();
        $fiscalYear = FiscalYear::query()->where('year', 2026)->first()
            ?? FiscalYear::query()->where('is_current', true)->first();

        $itsDivision = Division::query()->where('code', 'ITS-DEV')->first();
        $gssDivision = Division::query()->where('code', 'GSS-SUP')->first();
        $endUser = User::query()->where('email', 'end.user@pms.gov.ph')->first();
        $divisionChief = User::query()->where('email', 'division.chief@pms.gov.ph')->first();
        $planningOfficer = User::query()->where('email', 'planning.officer@pms.gov.ph')->first();

        if (! $fiscalYear || ! $itsDivision || ! $gssDivision) {
            $this->command?->warn('Skipping MarketScopingSeeder: run OrganizationSeeder and SampleProcurementSeeder first.');

            return;
        }

        MarketScoping::query()->firstOrCreate(
            [
                'fiscal_year_id' => $fiscalYear->id,
                'division_id' => $itsDivision->id,
                'project_name' => 'Procurement of Desktop Computers for Systems Development Division',
            ],
            [
                'control_no' => 'MSC-2026-DEV001',
                'procuring_entity' => $agency->displayName(),
                'end_user_unit' => $itsDivision->name,
                'representative_name' => $endUser?->name ?? 'Maria Clara Santos',
                'representative_designation' => $endUser?->position ?? 'Administrative Officer III',
                'estimated_budget' => 1100000.00,
                'period_from' => '2025-10-01',
                'period_to' => '2025-12-31',
                'expected_delivery' => '2026-04-30',
                'activities' => [
                    'consultations' => [
                        'checked' => true,
                        'documentation' => 'Meeting minutes with three authorized distributors (Oct 15 & Nov 8, 2025). Attendance sheets and quotation summaries attached.',
                        'description' => '',
                    ],
                    'summits' => [
                        'checked' => true,
                        'documentation' => 'IT Procurement Summit 2025 — registration confirmation and session notes on enterprise desktop standards.',
                        'description' => '',
                    ],
                    'reports' => [
                        'checked' => true,
                        'documentation' => 'Gartner PC Lifecycle Market Guide (Q4 2025 excerpt) and agency ICT refresh study.',
                        'description' => '',
                    ],
                    'brochures' => [
                        'checked' => true,
                        'documentation' => 'Product datasheets for Core i7 / 16GB RAM configurations from Dell, HP, and Lenovo.',
                        'description' => '',
                    ],
                    'price_sourcing' => [
                        'checked' => true,
                        'documentation' => 'Canvass Sheet CS-2025-ICT-014 with three compliant quotations; average unit cost ₱55,000.',
                        'description' => '',
                    ],
                    'philgeps' => [
                        'checked' => true,
                        'documentation' => 'PhilGEPS award history for similar desktop packages (2024–2025) and agency website price references.',
                        'description' => '',
                    ],
                    'other' => [
                        'checked' => false,
                        'documentation' => '',
                        'description' => '',
                    ],
                ],
                'parameters' => [
                    'cost_estimate' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Estimated ABC of ₱1,100,000.00 (20 units × ₱55,000) aligns with current market quotations and PhilGEPS benchmarks.',
                    ],
                    'design_spec' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'At least three suppliers can meet Core i7, 16GB RAM, SSD, and three-year warranty requirements.',
                    ],
                    'technical_criteria' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Market supports proposed specs; recommend retaining minimum processor generation and on-site warranty in the PPMP item description.',
                    ],
                    'delivery_lead_time' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Suppliers quoted 45–60 calendar days delivery; schedule PPMP procurement activity from March to April 2026.',
                    ],
                    'storage_warehousing' => [
                        'considered' => MarketScopingConsidered::NotApplicable->value,
                        'recommendations' => 'Standard office equipment; no special storage conditions required beyond existing property section capacity.',
                    ],
                    'risks' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Moderate price volatility noted for imported components; recommend early procurement and price validity of at least 90 days in bid documents.',
                    ],
                ],
                'status' => MarketScopingStatus::Approved,
                'prepared_by' => $endUser?->id,
                'approved_by' => $divisionChief?->id,
                'approved_at' => now()->subDays(14),
                'remarks' => 'Sample approved checklist supporting the ITS FY 2026 PPMP desktop computer line item.',
            ]
        );

        MarketScoping::query()->firstOrCreate(
            [
                'fiscal_year_id' => $fiscalYear->id,
                'division_id' => $gssDivision->id,
                'project_name' => 'Procurement of Office Supplies and Consumables FY 2026',
            ],
            [
                'control_no' => 'MSC-2026-GSS001',
                'procuring_entity' => $agency->displayName(),
                'end_user_unit' => $gssDivision->name,
                'representative_name' => $planningOfficer?->name ?? 'Ana Patricia Reyes',
                'representative_designation' => $planningOfficer?->position ?? 'Planning Officer III',
                'estimated_budget' => 350000.00,
                'period_from' => '2026-01-15',
                'period_to' => '2026-02-28',
                'expected_delivery' => '2026-06-30',
                'activities' => [
                    'consultations' => [
                        'checked' => true,
                        'documentation' => 'Phone and email inquiries with National Book Store, Office Warehouse, and local suppliers.',
                        'description' => '',
                    ],
                    'summits' => [
                        'checked' => false,
                        'documentation' => '',
                        'description' => '',
                    ],
                    'reports' => [
                        'checked' => false,
                        'documentation' => '',
                        'description' => '',
                    ],
                    'brochures' => [
                        'checked' => true,
                        'documentation' => '2026 corporate price lists and promotional catalogs for bond paper, toner, and filing supplies.',
                        'description' => '',
                    ],
                    'price_sourcing' => [
                        'checked' => true,
                        'documentation' => 'Canvass Sheet CS-2026-GSS-003 covering 15 commonly used supply items.',
                        'description' => '',
                    ],
                    'philgeps' => [
                        'checked' => true,
                        'documentation' => 'PhilGEPS postings for office supplies contracts in NCR agencies (Jan 2026).',
                        'description' => '',
                    ],
                    'other' => [
                        'checked' => true,
                        'documentation' => 'Walk-through inventory of prior year consumption per cost center.',
                        'description' => 'Annual consumption review with Supply and Property Division',
                    ],
                ],
                'parameters' => [
                    'cost_estimate' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => '₱350,000.00 lump-sum ABC is reasonable based on canvass and prior-year utilization plus 5% inflation allowance.',
                    ],
                    'design_spec' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Multiple suppliers available for standard-brand office supplies; attach generic specifications in the PPMP.',
                    ],
                    'technical_criteria' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Market can support brand-neutral specs with ISO-compliant paper and OEM-equivalent toner where applicable.',
                    ],
                    'delivery_lead_time' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Rolling delivery within 15 days upon order is feasible; recommend quarterly drawdown in the PPMP schedule.',
                    ],
                    'storage_warehousing' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Existing supply room can accommodate one quarter stock; monitor humidity for paper products.',
                    ],
                    'risks' => [
                        'considered' => MarketScopingConsidered::Yes->value,
                        'recommendations' => 'Limited risk; note possible toner supply delays — include alternative brand clause in specifications.',
                    ],
                ],
                'status' => MarketScopingStatus::Draft,
                'prepared_by' => $planningOfficer?->id,
                'approved_by' => null,
                'approved_at' => null,
                'remarks' => 'Draft sample checklist for GSS office supplies PPMP planning.',
            ]
        );

        $this->command?->info('Sample Market Scoping checklists seeded (1 approved ITS, 1 draft GSS).');
    }
}
