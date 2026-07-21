<?php

namespace Database\Seeders;

use App\Enums\MarketScopingStatus;
use App\Enums\ProjectProposalPipelineStep;
use App\Enums\ProjectProposalStatus;
use App\Models\Planning\MarketScoping;
use App\Models\Planning\ProjectProposal;
use App\Models\Settings\AgencyProfile;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds twenty sample Project Proposals (NMP-PP-01) with rich-text narrative
 * sections across all agency divisions and varied pipeline stages.
 */
class ProjectProposalSeeder extends Seeder
{
    public function run(): void
    {
        $agency = AgencyProfile::current();
        $fiscalYear = FiscalYear::query()->where('year', 2026)->first()
            ?? FiscalYear::query()->where('is_current', true)->first();

        $divisions = Division::query()->whereIn('code', [
            'ITS-DEV', 'ITS-NET', 'GSS-SUP', 'GSS-BAC', 'FMS-BUD', 'FMS-ACC', 'FMS-CSH',
            'OSEC-PLN', 'OSEC-LEG',
        ])->get()->keyBy('code');

        $endUser = User::query()->where('email', 'end.user@pms.gov.ph')->first();
        $divisionChief = User::query()->where('email', 'division.chief@pms.gov.ph')->first();
        $planningOfficer = User::query()->where('email', 'planning.officer@pms.gov.ph')->first();
        $hope = User::query()->where('email', 'hope@pms.gov.ph')->first();

        if (! $fiscalYear || $divisions->isEmpty()) {
            $this->command?->warn('Skipping ProjectProposalSeeder: run OrganizationSeeder first.');

            return;
        }

        $samples = $this->samples($agency->displayName());

        foreach ($samples as $index => $sample) {
            $division = $divisions->get($sample['division_code']);
            if (! $division) {
                continue;
            }

            $pipelineStep = $sample['pipeline_step'] ?? $this->defaultPipelineStep($sample['status'], $sample['control_no']);

            $proposal = ProjectProposal::query()->firstOrCreate(
                ['control_no' => $sample['control_no']],
                [
                    'market_scoping_id' => null,
                    'fiscal_year_id' => $fiscalYear->id,
                    'division_id' => $division->id,
                    'document_ref' => 'NMP-PP-01',
                    'with_enclosures' => $sample['with_enclosures'],
                    'project_type' => $sample['project_type'],
                    'title' => $sample['title'],
                    'schedule' => $sample['schedule'],
                    'venue_area' => $sample['venue_area'],
                    'total_cost' => $sample['total_cost'],
                    'fund_source_text' => $sample['fund_source_text'],
                    'proponent' => $sample['proponent'],
                    'rationale' => $sample['rationale'],
                    'objectives' => $sample['objectives'],
                    'target_schedule' => $sample['target_schedule'],
                    'budgetary_requirement' => $sample['budgetary_requirement'],
                    'fund_source_narrative' => $sample['fund_source_narrative'],
                    'status' => $sample['status'],
                    'pipeline_step' => $pipelineStep,
                    'prepared_by' => $endUser?->id,
                    'submitted_at' => $sample['status'] !== ProjectProposalStatus::Draft ? now()->subDays(20 - $index) : null,
                    'recommended_by' => in_array($sample['status'], [ProjectProposalStatus::ForRecommendation, ProjectProposalStatus::Approved], true) ? $planningOfficer?->id : null,
                    'recommended_at' => in_array($sample['status'], [ProjectProposalStatus::ForRecommendation, ProjectProposalStatus::Approved], true) ? now()->subDays(15 - $index) : null,
                    'approved_by' => $sample['status'] === ProjectProposalStatus::Approved ? $hope?->id : null,
                    'approved_at' => $sample['status'] === ProjectProposalStatus::Approved ? now()->subDays(10 - $index) : null,
                    'remarks' => $sample['remarks'],
                ]
            );

            if ($pipelineStep === ProjectProposalPipelineStep::ProjectProposal && $sample['status'] === ProjectProposalStatus::Draft) {
                continue;
            }

            $marketScoping = MarketScoping::query()->firstOrCreate(
                [
                    'fiscal_year_id' => $fiscalYear->id,
                    'division_id' => $division->id,
                    'project_name' => $sample['market_scoping_title'],
                ],
                [
                    'project_proposal_id' => $proposal->id,
                    'control_no' => 'MSC-2026-PP'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'procuring_entity' => $agency->displayName(),
                    'end_user_unit' => $division->name,
                    'representative_name' => $sample['proponent'],
                    'representative_designation' => 'Administrative Officer',
                    'estimated_budget' => $sample['total_cost'],
                    'period_from' => '2025-10-01',
                    'period_to' => '2025-12-31',
                    'expected_delivery' => '2026-06-30',
                    'activities' => ['consultations' => ['checked' => true, 'documentation' => 'Sample market scoping activity.', 'description' => '']],
                    'parameters' => ['cost_estimate' => ['considered' => 'yes', 'recommendations' => 'Cost estimate validated.']],
                    'status' => MarketScopingStatus::Approved,
                    'prepared_by' => $endUser?->id,
                    'approved_by' => $divisionChief?->id,
                    'approved_at' => now()->subDays(30 - $index),
                ]
            );

            $marketScoping->update(['project_proposal_id' => $proposal->id]);

            if ($pipelineStep === ProjectProposalPipelineStep::MarketScoping) {
                continue;
            }

            $proposal->update(['market_scoping_id' => $marketScoping->id]);
        }

        $this->command?->info('Sample Project Proposals seeded ('.count($samples).' records with rich-text narratives).');
    }

    protected function defaultPipelineStep(ProjectProposalStatus $status, string $controlNo): ProjectProposalPipelineStep
    {
        if ($status === ProjectProposalStatus::ReturnedForRevision) {
            return ProjectProposalPipelineStep::ProjectProposal;
        }

        if ($status !== ProjectProposalStatus::Draft) {
            return ProjectProposalPipelineStep::Completed;
        }

        return match ($controlNo) {
            'PP-2026-003', 'PP-2026-014' => ProjectProposalPipelineStep::MarketScoping,
            'PP-2026-008', 'PP-2026-017' => ProjectProposalPipelineStep::IndicativePpmp,
            default => ProjectProposalPipelineStep::ProjectProposal,
        };
    }

    /** @return array<int, array<string, mixed>> */
    protected function samples(string $agencyName): array
    {
        return [
            $this->sample(
                controlNo: 'PP-2026-001',
                divisionCode: 'ITS-DEV',
                marketTitle: 'Procurement of Desktop Computers for Systems Development Division',
                title: 'Desktop Computer Refresh Program FY 2026',
                projectType: 'Goods',
                schedule: 'March 2026 – April 2026',
                venue: 'Systems Development Division, NMP',
                cost: 1100000.00,
                fundSource: 'MOOE',
                proponent: 'Maria Clara Santos',
                enclosures: 'Market Scoping Checklist MSC-2026-DEV001',
                status: ProjectProposalStatus::Approved,
                rationale: '<p>The Systems Development Division requires <strong>twenty (20) desktop units</strong> to replace aging workstations that no longer meet minimum development environment standards.</p><ul><li>Current units are beyond economical repair</li><li>Productivity loss due to hardware failures</li><li>Supports agency digital transformation goals</li></ul>',
                objectives: '<h3>General Objective</h3><p>Procure and deploy compliant desktop computers for SDD personnel.</p><h3>Specific Objectives</h3><ol><li>Acquire 20 units meeting Core i7 / 16GB RAM specifications</li><li>Complete deployment by April 2026</li><li>Achieve at least 99% uptime post-deployment</li></ol>',
                targetSchedule: '<p><strong>Q1 2026:</strong> PPMP approval and PR preparation<br><strong>March 2026:</strong> Bidding and award<br><strong>April 2026:</strong> Delivery, inspection, and acceptance</p>',
                budgetary: '<p>Breakdown of estimated costs:</p><table><thead><tr><th>Item</th><th>Qty</th><th>Unit Cost</th><th>Amount</th></tr></thead><tbody><tr><td>Desktop Computer</td><td>20</td><td>₱55,000.00</td><td>₱1,100,000.00</td></tr></tbody></table>',
                fundNarrative: '<p>Funding shall be charged against the agency <strong>MOOE</strong> allocation for ICT equipment under the approved GAA FY 2026.</p>',
                remarks: 'Approved sample linked to indicative PPMP pipeline.',
            ),
            $this->sample(
                controlNo: 'PP-2026-002',
                divisionCode: 'ITS-NET',
                marketTitle: 'Network Infrastructure Upgrade Phase 1',
                title: 'Core Network Switch Replacement FY 2026',
                projectType: 'Infrastructure',
                schedule: 'May 2026 – June 2026',
                venue: 'Network & Infrastructure Division',
                cost: 450000.00,
                fundSource: 'MOOE',
                proponent: 'Engr. Roberto Mendoza',
                enclosures: 'Technical survey report, Market Scoping Checklist',
                status: ProjectProposalStatus::ForRecommendation,
                rationale: '<p>Legacy network switches have reached end-of-support, increasing downtime risk for museum operations and public-facing digital services.</p><blockquote>Network reliability is critical to visitor ticketing, collections management, and security systems.</blockquote>',
                objectives: '<ul><li>Replace aging 24-port managed switches with current-generation equipment</li><li>Improve backbone bandwidth and VLAN segmentation</li><li>Reduce mean time to repair by 40%</li></ul>',
                targetSchedule: '<ol><li><strong>May 2026</strong> — Procurement activity</li><li><strong>June 2026</strong> — Installation and cutover</li><li><strong>June 2026</strong> — Post-implementation review</li></ol>',
                budgetary: '<p>Estimated requirement: <strong>₱450,000.00</strong> for ten (10) managed switches including installation consumables.</p>',
                fundNarrative: '<p>Charged to ICT infrastructure MOOE sub-object under Fund 101.</p>',
                remarks: 'Pending Director-General approval.',
            ),
            $this->sample(
                controlNo: 'PP-2026-003',
                divisionCode: 'GSS-SUP',
                marketTitle: 'Procurement of Office Supplies and Consumables FY 2026',
                title: 'Annual Office Supplies Procurement FY 2026',
                projectType: 'Goods',
                schedule: 'January 2026 – December 2026',
                venue: 'Supply and Property Division',
                cost: 350000.00,
                fundSource: 'MOOE',
                proponent: 'Ana Patricia Reyes',
                enclosures: 'Consumption report FY 2025',
                status: ProjectProposalStatus::Draft,
                rationale: '<p>Regular replenishment of office supplies is necessary to sustain uninterrupted administrative operations across all NMP divisions.</p>',
                objectives: '<p>Ensure adequate stock of bond paper, toner, filing materials, and common consumables through a single annual procurement.</p>',
                targetSchedule: '<p>Rolling quarterly delivery from <strong>Q2 to Q4 2026</strong> based on requisition schedules.</p>',
                budgetary: '<p>Lump-sum ABC of <strong>₱350,000.00</strong> based on FY 2025 utilization plus 5% inflation allowance.</p>',
                fundNarrative: '<p>MOOE — general supplies and materials.</p>',
                remarks: 'Draft for division review.',
            ),
            $this->sample(
                controlNo: 'PP-2026-004',
                divisionCode: 'FMS-BUD',
                marketTitle: 'Procurement of Financial Management Software License',
                title: 'Budget Management System License Renewal FY 2026',
                projectType: 'Consulting / IT Services',
                schedule: 'February 2026',
                venue: 'Budget Division, FMS',
                cost: 280000.00,
                fundSource: 'MOOE',
                proponent: 'Carlos Miguel Rivera',
                enclosures: 'License quotation, TOR',
                status: ProjectProposalStatus::Approved,
                rationale: '<p>The existing budget management module license expires in March 2026. Non-renewal would disrupt PPMP, APP, and obligation tracking workflows mandated under RA 12009.</p>',
                objectives: '<ul><li>Renew enterprise license for 50 concurrent users</li><li>Maintain integration with agency GAA and PPMP modules</li></ul>',
                targetSchedule: '<p>License activation target: <strong>28 February 2026</strong></p>',
                budgetary: '<p>Annual subscription: <strong>₱280,000.00</strong> inclusive of VAT.</p>',
                fundNarrative: '<p>Charged to MOOE — computer software subscription.</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-005',
                divisionCode: 'OSEC-PLN',
                marketTitle: 'Museum Exhibition Fit-Out Materials',
                title: 'Special Exhibition Fit-Out FY 2026',
                projectType: 'Goods / Services',
                schedule: 'July 2026 – September 2026',
                venue: 'National Museum Main Building',
                cost: 1250000.00,
                fundSource: 'MOOE / Trust Fund',
                proponent: 'Jorell M. Legaspi',
                enclosures: 'Exhibition design brief, Market Scoping',
                status: ProjectProposalStatus::ForRecommendation,
                rationale: '<p>A flagship temporary exhibition requires specialized display cases, lighting, and interpretive panels to meet international museum standards.</p><ul><li>Enhances public engagement</li><li>Supports cultural heritage mandate</li></ul>',
                objectives: '<ol><li>Procure and install exhibition fit-out materials</li><li>Complete installation before opening week</li><li>Ensure compliance with conservation-grade specifications</li></ol>',
                targetSchedule: '<p><strong>July 2026</strong> — Procurement<br><strong>August 2026</strong> — Fabrication<br><strong>September 2026</strong> — Installation and turnover</p>',
                budgetary: '<p>Total ABC: <strong>₱1,250,000.00</strong> covering display systems, lighting, and installation services.</p>',
                fundNarrative: '<p>Primary charge to MOOE; partial cost-sharing from approved trust fund for special exhibitions.</p>',
                remarks: 'Recommended for HOPE approval.',
            ),
            $this->sample(
                controlNo: 'PP-2026-006',
                divisionCode: 'ITS-DEV',
                marketTitle: 'Procurement of Application Security Assessment Services',
                title: 'Web Application Penetration Testing FY 2026',
                projectType: 'Consulting Services',
                schedule: 'April 2026',
                venue: 'ODG-ICT / Remote',
                cost: 185000.00,
                fundSource: 'MOOE',
                proponent: 'Resty D. Morancil',
                enclosures: 'TOR, Market Scoping Checklist',
                status: ProjectProposalStatus::Draft,
                rationale: '<p>Annual security assessment is required for internet-facing museum portals and procurement systems to mitigate cybersecurity risks.</p>',
                objectives: '<p>Engage a qualified firm to conduct OWASP-based penetration testing and provide remediation recommendations.</p>',
                targetSchedule: '<p>Assessment window: <strong>15–30 April 2026</strong>; final report due <strong>15 May 2026</strong>.</p>',
                budgetary: '<p>Professional fee ceiling: <strong>₱185,000.00</strong> for up to five (5) applications.</p>',
                fundNarrative: '<p>MOOE — professional services.</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-007',
                divisionCode: 'GSS-SUP',
                marketTitle: 'Procurement of Janitorial Supplies and Equipment',
                title: 'Janitorial Supplies and Equipment FY 2026',
                projectType: 'Goods',
                schedule: 'March 2026 – June 2026',
                venue: 'All NMP Sites',
                cost: 420000.00,
                fundSource: 'MOOE',
                proponent: 'Lucia Fernandez',
                enclosures: 'Inventory report',
                status: ProjectProposalStatus::Approved,
                rationale: '<p>Consolidated procurement of janitorial supplies reduces unit cost and ensures consistent quality across museum sites.</p>',
                objectives: '<ul><li>Procure cleaning agents, tools, and PPE for six-month consumption</li><li>Standardize specifications agency-wide</li></ul>',
                targetSchedule: '<p>Bi-monthly delivery schedule from March to June 2026.</p>',
                budgetary: '<p>ABC: <strong>₱420,000.00</strong> based on consolidated consumption forecast.</p>',
                fundNarrative: '<p>Charged to MOOE — janitorial supplies sub-object.</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-008',
                divisionCode: 'ITS-NET',
                marketTitle: 'Procurement of UPS and Power Protection Devices',
                title: 'Data Center UPS Replacement FY 2026',
                projectType: 'Goods',
                schedule: 'June 2026',
                venue: 'NMP Data Center',
                cost: 620000.00,
                fundSource: 'MOOE',
                proponent: 'Engr. Roberto Mendoza',
                enclosures: 'Load assessment report',
                status: ProjectProposalStatus::Draft,
                rationale: '<p>Existing UPS units are at end-of-life and cannot sustain required runtime during power interruptions, risking data loss.</p>',
                objectives: '<p>Procure redundant UPS systems with minimum 30-minute runtime at full load.</p>',
                targetSchedule: '<p>Delivery and commissioning targeted for <strong>June 2026</strong>.</p>',
                budgetary: '<p>Two (2) rack-mounted UPS units — <strong>₱620,000.00</strong> total.</p>',
                fundNarrative: '<p>MOOE — ICT equipment.</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-009',
                divisionCode: 'OSEC-PLN',
                marketTitle: 'Procurement of Planning and Research Database Subscription',
                title: 'Policy Research Database Subscription FY 2026',
                projectType: 'Consulting / IT Services',
                schedule: 'January 2026 – December 2026',
                venue: 'Planning Division',
                cost: 95000.00,
                fundSource: 'MOOE',
                proponent: 'Planning Officer III',
                enclosures: 'Subscription quotation',
                status: ProjectProposalStatus::ForRecommendation,
                rationale: '<p>Access to updated procurement and governance research databases supports APP and PPMP formulation aligned with GPPB issuances.</p>',
                objectives: '<p>Maintain annual subscription for three (3) concurrent planner accounts.</p>',
                targetSchedule: '<p>Subscription period: <strong>Calendar Year 2026</strong>.</p>',
                budgetary: '<p>Annual fee: <strong>₱95,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — library and research subscriptions.</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-010',
                divisionCode: 'FMS-BUD',
                marketTitle: 'Procurement of Document Management System Upgrade',
                title: 'Electronic Document Management Upgrade FY 2026',
                projectType: 'Infrastructure / IT Services',
                schedule: 'August 2026 – October 2026',
                venue: 'Finance and Management Service',
                cost: 890000.00,
                fundSource: 'MOOE',
                proponent: 'Carlos Miguel Rivera',
                enclosures: 'EDMS assessment, Market Scoping',
                status: ProjectProposalStatus::Approved,
                rationale: '<p>The current EDMS lacks workflow integration with procurement documents (PPMP, PR, PO), causing manual routing delays.</p><ul><li>Supports paperless government directive</li><li>Improves audit trail completeness</li></ul>',
                objectives: '<ol><li>Upgrade EDMS modules for procurement document routing</li><li>Migrate 50,000+ legacy records</li><li>Train 120 end-users</li></ol>',
                targetSchedule: '<p><strong>Aug 2026</strong> — Award<br><strong>Sep 2026</strong> — Implementation<br><strong>Oct 2026</strong> — Go-live</p>',
                budgetary: '<p>Total project cost: <strong>₱890,000.00</strong> (software, migration, training).</p>',
                fundNarrative: '<p>MOOE — ICT systems development and maintenance.</p>',
                remarks: 'Approved for indicative PPMP generation.',
            ),
            $this->sample(
                controlNo: 'PP-2026-011',
                divisionCode: 'OSEC-LEG',
                marketTitle: 'Legal Research and Jurisprudence Database Subscription',
                title: 'Legal Research Database FY 2026',
                projectType: 'Consulting / IT Services',
                schedule: 'January 2026 – December 2026',
                venue: 'Legal Division',
                cost: 120000.00,
                fundSource: 'MOOE',
                proponent: 'Atty. Elena Villanueva',
                enclosures: 'Subscription quotation, TOR',
                status: ProjectProposalStatus::ForRecommendation,
                rationale: '<p>The Legal Division requires access to updated statutes, GPPB issuances, and jurisprudence databases to support procurement legal opinions and contract review.</p>',
                objectives: '<ul><li>Maintain three (3) concurrent legal researcher accounts</li><li>Enable full-text search of RA 12009 and IRR annotations</li></ul>',
                targetSchedule: '<p>Subscription period: <strong>Calendar Year 2026</strong>.</p>',
                budgetary: '<p>Annual subscription fee: <strong>₱120,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — library and research subscriptions.</p>',
                remarks: 'Endorsed by Legal Division Chief.',
            ),
            $this->sample(
                controlNo: 'PP-2026-012',
                divisionCode: 'OSEC-LEG',
                marketTitle: 'Procurement of Legal Forms and Stationery',
                title: 'Legal Division Office Supplies FY 2026',
                projectType: 'Goods',
                schedule: 'February 2026 – March 2026',
                venue: 'Legal Division',
                cost: 75000.00,
                fundSource: 'MOOE',
                proponent: 'Atty. Elena Villanueva',
                enclosures: 'Consumption report FY 2025',
                status: ProjectProposalStatus::Draft,
                rationale: '<p>Replenishment of legal pads, notarial seals, document folders, and printing supplies for contract management activities.</p>',
                objectives: '<p>Procure six-month stock of legal division consumables through consolidated SVP.</p>',
                targetSchedule: '<p>Delivery target: <strong>March 2026</strong>.</p>',
                budgetary: '<p>Lump-sum ABC: <strong>₱75,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — general supplies.</p>',
                remarks: 'Draft — Step 1 only.',
            ),
            $this->sample(
                controlNo: 'PP-2026-013',
                divisionCode: 'FMS-ACC',
                marketTitle: 'Procurement of Accounting Information System License',
                title: 'General Ledger Module License Renewal FY 2026',
                projectType: 'Consulting / IT Services',
                schedule: 'March 2026',
                venue: 'Accounting Division, FMS',
                cost: 320000.00,
                fundSource: 'MOOE',
                proponent: 'Rosario Mendoza',
                enclosures: 'License quotation, EDMS cross-reference',
                status: ProjectProposalStatus::Approved,
                rationale: '<p>The agency general ledger module license expires in April 2026. Renewal is required for uninterrupted financial reporting and UACS-compliant bookkeeping.</p>',
                objectives: '<ol><li>Renew license for 35 accounting staff</li><li>Maintain COA-reporting integration</li></ol>',
                targetSchedule: '<p>Activation before <strong>31 March 2026</strong>.</p>',
                budgetary: '<p>Annual license: <strong>₱320,000.00</strong> inclusive of VAT.</p>',
                fundNarrative: '<p>MOOE — computer software subscription.</p>',
                remarks: 'Approved; pipeline complete.',
            ),
            $this->sample(
                controlNo: 'PP-2026-014',
                divisionCode: 'FMS-ACC',
                marketTitle: 'Procurement of Audit Working Papers and Forms',
                title: 'Audit Documentation Supplies FY 2026',
                projectType: 'Goods',
                schedule: 'April 2026',
                venue: 'Accounting Division',
                cost: 48000.00,
                fundSource: 'MOOE',
                proponent: 'Rosario Mendoza',
                enclosures: 'Inventory report',
                status: ProjectProposalStatus::Draft,
                rationale: '<p>Standardized audit working paper templates and binders support internal audit and COA documentary requirements.</p>',
                objectives: '<p>Procure audit forms, binders, and labels for FY 2026 audit cycle.</p>',
                targetSchedule: '<p>Delivery: <strong>April 2026</strong>.</p>',
                budgetary: '<p>ABC: <strong>₱48,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — supplies and materials.</p>',
                remarks: 'At pipeline Step 2 — Market Scoping.',
            ),
            $this->sample(
                controlNo: 'PP-2026-015',
                divisionCode: 'FMS-CSH',
                marketTitle: 'Armored Car Service for Cash Collections',
                title: 'Cash-in-Transit Security Service FY 2026',
                projectType: 'Consulting Services',
                schedule: 'January 2026 – December 2026',
                venue: 'All NMP Sites',
                cost: 540000.00,
                fundSource: 'MOOE',
                proponent: 'Antonio Delgado',
                enclosures: 'TOR, Market Scoping Checklist',
                status: ProjectProposalStatus::ForRecommendation,
                rationale: '<p>Secure transport of museum admission collections and official receipts from satellite sites to the Cash Division is required under agency internal control policies.</p>',
                objectives: '<ul><li>Engage BSP-accredited armored car service provider</li><li>Twice-weekly scheduled collections from three sites</li></ul>',
                targetSchedule: '<p>Contract period: <strong>CY 2026</strong>.</p>',
                budgetary: '<p>Annual service fee: <strong>₱540,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — security services.</p>',
                remarks: 'Pending HOPE recommendation.',
            ),
            $this->sample(
                controlNo: 'PP-2026-016',
                divisionCode: 'GSS-BAC',
                marketTitle: 'PhilGEPS Posting and Bid Document Preparation Support',
                title: 'BAC Secretariat Support Services FY 2026',
                projectType: 'Consulting Services',
                schedule: 'January 2026 – December 2026',
                venue: 'BAC Secretariat Division',
                cost: 675000.00,
                fundSource: 'MOOE',
                proponent: 'BAC Secretariat Officer',
                enclosures: 'TOR, GPPB Circular references',
                status: ProjectProposalStatus::Approved,
                rationale: '<p>Supplemental manpower support for PhilGEPS posting, bid bulletin preparation, and abstract of bids documentation during peak procurement season.</p>',
                objectives: '<p>Provide two (2) contractual BAC support staff for twelve months.</p>',
                targetSchedule: '<p>Contract start: <strong>January 2026</strong>.</p>',
                budgetary: '<p>Total professional fees: <strong>₱675,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — professional services (BAC operations).</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-017',
                divisionCode: 'GSS-BAC',
                marketTitle: 'Procurement of BAC Meeting Supplies and Equipment',
                title: 'BAC Session Equipment and Supplies FY 2026',
                projectType: 'Goods',
                schedule: 'May 2026',
                venue: 'BAC Conference Room',
                cost: 155000.00,
                fundSource: 'MOOE',
                proponent: 'BAC Secretariat Officer',
                enclosures: 'Equipment inventory',
                status: ProjectProposalStatus::Draft,
                rationale: '<p>Replacement of worn conference microphones, projection accessories, and BAC session document kits.</p>',
                objectives: '<p>Procure BAC session equipment lot for improved bid evaluation hearings.</p>',
                targetSchedule: '<p>Delivery: <strong>May 2026</strong>.</p>',
                budgetary: '<p>ABC: <strong>₱155,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — equipment outlay.</p>',
                remarks: 'At pipeline Step 3 — Indicative PPMP.',
            ),
            $this->sample(
                controlNo: 'PP-2026-018',
                divisionCode: 'ITS-DEV',
                marketTitle: 'Procurement of DevOps and CI/CD Platform Subscription',
                title: 'Software Delivery Pipeline Tools FY 2026',
                projectType: 'Consulting / IT Services',
                schedule: 'February 2026 – January 2027',
                venue: 'Systems Development Division',
                cost: 395000.00,
                fundSource: 'MOOE',
                proponent: 'Resty D. Morancil',
                enclosures: 'Technical evaluation, Market Scoping',
                status: ProjectProposalStatus::ForRecommendation,
                rationale: '<p>Centralized CI/CD tooling reduces deployment errors for museum digital services and supports DevSecOps compliance.</p><ul><li>Automated testing for web applications</li><li>Version-controlled release management</li></ul>',
                objectives: '<ol><li>Subscribe to enterprise DevOps platform for 15 developers</li><li>Integrate with existing Git repository</li></ol>',
                targetSchedule: '<p>Go-live: <strong>March 2026</strong>.</p>',
                budgetary: '<table><thead><tr><th>Item</th><th>Amount</th></tr></thead><tbody><tr><td>Annual platform subscription</td><td>₱395,000.00</td></tr></tbody></table>',
                fundNarrative: '<p>MOOE — ICT software subscriptions.</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-019',
                divisionCode: 'GSS-SUP',
                marketTitle: 'Procurement of Motor Vehicle Maintenance Services',
                title: 'Fleet Maintenance Services FY 2026',
                projectType: 'Consulting Services',
                schedule: 'January 2026 – December 2026',
                venue: 'All NMP Sites',
                cost: 580000.00,
                fundSource: 'MOOE',
                proponent: 'Lucia Fernandez',
                enclosures: 'Fleet inventory, Market Scoping',
                status: ProjectProposalStatus::Approved,
                rationale: '<p>Preventive maintenance and repair of twelve (12) agency service vehicles ensures reliable transport for procurement deliveries and site inspections.</p>',
                objectives: '<p>Engage accredited automotive service provider for annual fleet maintenance contract.</p>',
                targetSchedule: '<p>Contract period: <strong>CY 2026</strong>.</p>',
                budgetary: '<p>Annual maintenance contract: <strong>₱580,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — repairs and maintenance of transportation equipment.</p>',
                remarks: null,
            ),
            $this->sample(
                controlNo: 'PP-2026-020',
                divisionCode: 'OSEC-PLN',
                marketTitle: 'Procurement of Strategic Planning Facilitation Services',
                title: 'Agency Strategic Plan Facilitation FY 2026',
                projectType: 'Consulting Services',
                schedule: 'September 2026',
                venue: 'National Museum Training Center',
                cost: 210000.00,
                fundSource: 'MOOE',
                proponent: 'Jorell M. Legaspi',
                enclosures: 'TOR, prior workshop evaluation',
                status: ProjectProposalStatus::ReturnedForRevision,
                rationale: '<p>External facilitation support is needed for the FY 2027–2029 agency strategic planning workshop involving division chiefs and key officials.</p>',
                objectives: '<p>Conduct three-day strategic planning workshop with documentation and action plan output.</p>',
                targetSchedule: '<p>Workshop dates: <strong>15–17 September 2026</strong>.</p>',
                budgetary: '<p>Professional fee ceiling: <strong>₱210,000.00</strong>.</p>',
                fundNarrative: '<p>MOOE — training and professional services.</p>',
                remarks: 'Returned by Planning — revise target schedule and ABC breakdown.',
                pipelineStep: ProjectProposalPipelineStep::ProjectProposal,
            ),
        ];
    }

    /** @return array<string, mixed> */
    protected function sample(
        string $controlNo,
        string $divisionCode,
        string $marketTitle,
        string $title,
        string $projectType,
        string $schedule,
        string $venue,
        float $cost,
        string $fundSource,
        string $proponent,
        string $enclosures,
        ProjectProposalStatus $status,
        string $rationale,
        string $objectives,
        string $targetSchedule,
        string $budgetary,
        string $fundNarrative,
        ?string $remarks,
        ?ProjectProposalPipelineStep $pipelineStep = null,
    ): array {
        return [
            'control_no' => $controlNo,
            'division_code' => $divisionCode,
            'market_scoping_title' => $marketTitle,
            'title' => $title,
            'project_type' => $projectType,
            'schedule' => $schedule,
            'venue_area' => $venue,
            'total_cost' => $cost,
            'fund_source_text' => $fundSource,
            'proponent' => $proponent,
            'with_enclosures' => $enclosures,
            'status' => $status,
            'pipeline_step' => $pipelineStep,
            'rationale' => $rationale,
            'objectives' => $objectives,
            'target_schedule' => $targetSchedule,
            'budgetary_requirement' => $budgetary,
            'fund_source_narrative' => $fundNarrative,
            'remarks' => $remarks,
        ];
    }
}
