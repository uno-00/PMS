<?php

namespace App\Support;

/**
 * Static content backing the Help & User Manuals module. Each module guide
 * includes purpose, step-by-step instructions with real UI screenshots,
 * workflow, errors, FAQs, approval routing, and tips.
 */
final class HelpContent
{
    /** @return array{text: string, screenshot?: string, caption?: string} */
    private static function step(string $text, ?string $screenshot = null, ?string $caption = null): array
    {
        $step = ['text' => $text];

        if ($screenshot) {
            $step['screenshot'] = $screenshot;
        }

        if ($caption) {
            $step['caption'] = $caption;
        }

        return $step;
    }

    public static function modules(): array
    {
        return [
            'getting-started' => [
                'label' => 'Getting Started',
                'purpose' => 'Orientation to the Procurement Management System (PMS): dashboards, navigation, column filters, and where to find help.',
                'steps' => [
                    self::step('Sign in at the agency login page using your assigned email and password.', 'images/help/dashboard/step-01-executive-dashboard.png', 'Executive Dashboard after login'),
                    self::step('Use the left sidebar to reach modules enabled for your role (Planning, Budget, BAC, Procurement, Settings, etc.).', 'images/help/dashboard/step-01-executive-dashboard.png', 'Sidebar navigation'),
                    self::step('On list pages, use the filter row under each column header to search records. Filter by Date Created using the From/To date pickers.', 'images/help/ppmp/step-01-ppmp-index.png', 'Per-column filters on PPMP list'),
                    self::step('Return to Help & User Manuals anytime from the sidebar for role-specific guides and screenshots.', 'images/help/dashboard/step-02-help-manuals.png', 'Help & User Manuals page'),
                ],
                'workflow' => null,
                'errors' => [
                    'Menu item missing — your role may not have permission, or a Super Admin deactivated the module in Settings > Module Management.',
                    'Blank page after login — complete the forced password change if prompted.',
                ],
                'faqs' => [
                    'Where are test accounts documented? See docs/TEST_ACCOUNTS.md in the project repository.',
                    'Can I bookmark a filtered list? Yes — column filters are stored in the URL.',
                ],
                'approval' => 'No approval required; all authenticated users with help.view can read manuals.',
                'tips' => 'Pick your Role Manual on the left to jump directly to modules relevant to your job.',
            ],
            'gaa' => [
                'label' => 'General Appropriations Act (GAA)',
                'purpose' => 'Record the fiscal year\'s DBM-issued General Appropriations Act and validate it before any budget can be allocated.',
                'steps' => [
                    self::step('Super Admin creates the Fiscal Year in Settings > Fiscal Years and marks the current year.', 'images/help/gaa/step-01-fiscal-years.png', 'Settings > Fiscal Years'),
                    self::step('Budget Officer opens GAA from the sidebar and reviews existing records for the fiscal year.', 'images/help/gaa/step-02-gaa-index.png', 'GAA list'),
                    self::step('Click Upload GAA, select the fiscal year, and upload the DBM Excel template. The system validates line items and flags mismatches.', 'images/help/gaa/step-03-gaa-upload.png', 'GAA upload form'),
                    self::step('Open the GAA record and review the Budget Summary and Budget Comparison tabs.', 'images/help/gaa/step-04-gaa-detail.png', 'GAA detail — Budget Summary'),
                    self::step('Submit for validation, then approval when totals reconcile with the DBM figure.', 'images/help/gaa/step-04-gaa-detail.png', 'GAA workflow actions'),
                    self::step('Once approved, distribute the GAA to generate the initial Budget Allocation tree.', 'images/help/gaa/step-04-gaa-detail.png', 'Distribute GAA action'),
                ],
                'workflow' => ['Draft', 'Validated', 'Approved', 'Distributed'],
                'errors' => [
                    'Excel template columns don\'t match the expected headers — re-download the latest template from GAA > Upload.',
                    'Total does not reconcile with DBM figure — check for duplicate or missing line items.',
                ],
                'faqs' => [
                    'Can I upload a GAA twice for the same fiscal year? No — one GAA per fiscal year; use a Supplemental Budget instead.',
                    'What happens after distribution? APP and Budget Allocations automatically inherit the approved amounts.',
                ],
                'approval' => 'Budget Officer uploads and validates → Budget Officer/Head approves → System distributes automatically.',
                'tips' => 'Validate early — fixing an Excel error before submission is much faster than requesting a re-upload after rejection.',
            ],
            'app' => [
                'label' => 'Annual Procurement Plan (APP)',
                'purpose' => 'Consolidate every division\'s planned procurement for the fiscal year into a single BAC-reviewed plan.',
                'steps' => [
                    self::step('An APP record is auto-created once the GAA for the fiscal year is approved.', 'images/help/app/step-01-app-overview.png', 'APP overview for fiscal year'),
                    self::step('Divisions submit PPMPs (via the Indicative PPMP pipeline or standalone PPMP) which roll up into the APP.', 'images/help/ppmp/step-01-ppmp-index.png', 'PPMP list feeding APP consolidation'),
                    self::step('BAC Secretariat reviews the consolidated plan totals and item breakdown on the APP page.', 'images/help/app/step-01-app-overview.png', 'APP consolidation view'),
                    self::step('HOPE/BAC approves; the APP is then locked for the rest of the fiscal year.', 'images/help/app/step-01-app-overview.png', 'APP approval workflow'),
                ],
                'workflow' => ['Draft', 'For Consolidation', 'BAC Review', 'Approved', 'Locked'],
                'errors' => ['APP shows ₱0 total — check that at least one PPMP has been submitted for the fiscal year.'],
                'faqs' => ['Can the APP be edited after Locked? No — raise a Supplemental APP instead.'],
                'approval' => 'Auto-created on GAA approval → BAC Review → Approved → Locked.',
                'tips' => 'Encourage divisions to submit PPMPs early in the fiscal year so BAC consolidation isn\'t rushed.',
            ],
            'budget-allocation' => [
                'label' => 'Budget Distribution / Allocation',
                'purpose' => 'Push the approved GAA amount down to departments, divisions, offices, cost centers, and PAPs while enforcing "no overallocation."',
                'steps' => [
                    self::step('Open Budget > Allocations and select the fiscal year from the filter row.', 'images/help/budget-allocation/step-01-allocations-index.png', 'Budget Allocations list'),
                    self::step('Select the parent node (e.g. the whole GAA) and click Allocate.', 'images/help/budget-allocation/step-01-allocations-index.png', 'Allocate sub-budget action'),
                    self::step('Choose the target department/division/cost center and enter the amount — the system rejects any amount exceeding the parent\'s remaining balance.', 'images/help/budget-allocation/step-01-allocations-index.png', 'Allocation modal'),
                    self::step('Repeat until the fiscal year\'s budget is fully distributed.', 'images/help/budget-allocation/step-01-allocations-index.png', 'Completed allocation tree'),
                ],
                'workflow' => null,
                'errors' => ['"Amount exceeds remaining balance" — the parent allocation doesn\'t have enough unallocated funds left.'],
                'faqs' => ['Can I reallocate later? Yes, reduce the source allocation first, then increase the destination.'],
                'approval' => 'Budget Officer allocates directly; every allocation is logged to the Audit Trail.',
                'tips' => 'Leave a small contingency unallocated at the top level for mid-year adjustments.',
            ],
            'project-proposal' => [
                'label' => 'Indicative PPMP Pipeline (Project Proposal)',
                'purpose' => 'Three-step procedure to initiate procurement planning: Project Proposal (NMP-PP-01) → Market Scoping Checklist → auto-generated Indicative PPMP.',
                'steps' => [
                    self::step('Open Indicative PPMP Pipeline from the sidebar. Click Start New Pipeline to begin Step 1.', 'images/help/project-proposal/step-01-pipeline-index.png', 'Pipeline index with stepper'),
                    self::step('Complete the Project Proposal form: title, objectives, schedule (month/year range), budget, and narrative sections. Use Generate Context from AI to draft sections II–VI.', 'images/help/project-proposal/step-02-proposal-form.png', 'Project Proposal form'),
                    self::step('Proceed to Step 2 — Market Scoping Checklist. Document supplier research, price benchmarks, and market availability.', 'images/help/project-proposal/step-03-market-scoping-index.png', 'Market Scoping list'),
                    self::step('Complete Step 3 — the system generates an Indicative PPMP from the approved proposal and market scoping data.', 'images/help/ppmp/step-02-ppmp-create.png', 'Indicative PPMP wizard'),
                    self::step('Submit the Indicative PPMP through Division Chief → Planning → Budget → BAC consolidation.', 'images/help/ppmp/step-01-ppmp-index.png', 'PPMP approval tracking'),
                    self::step('Once Approved and Locked, PPMP line items become available for Purchase Requests.', 'images/help/ppmp/step-01-ppmp-index.png', 'Locked PPMP ready for PR'),
                ],
                'workflow' => ['Project Proposal', 'Market Scoping', 'Indicative PPMP', 'Division Chief', 'Planning', 'Budget', 'BAC Consolidation', 'Approved', 'Locked'],
                'errors' => [
                    'Cannot proceed to Market Scoping — complete all required Project Proposal fields first.',
                    'Indicative PPMP not generated — ensure Market Scoping checklist is submitted.',
                ],
                'faqs' => [
                    'Can I skip Market Scoping? No — it is Step 2 of the mandatory three-step pipeline.',
                    'Is this different from a Final PPMP? Yes — Indicative PPMPs come from the pipeline; Final PPMPs can also be created standalone.',
                ],
                'approval' => 'Project Proposal (draft) → Market Scoping → Indicative PPMP → standard PPMP approval chain.',
                'tips' => 'Use the pipeline stepper at the top of each screen to see which step you are on and what comes next.',
            ],
            'ppmp' => [
                'label' => 'Project Procurement Management Plan (PPMP)',
                'purpose' => 'Each division\'s itemized procurement plan for the fiscal year — the sole source from which Purchase Requests may be created.',
                'steps' => [
                    self::step('Division staff opens PPMP > Create (or continues from the Indicative PPMP Pipeline).', 'images/help/ppmp/step-01-ppmp-index.png', 'PPMP list with PPMP Type column'),
                    self::step('Add items: description, specification, unit, quantity, ABC, schedule, mode of procurement, fund source, PAP, and UACS.', 'images/help/ppmp/step-02-ppmp-create.png', 'PPMP item entry form'),
                    self::step('Submit for Division Chief review, then Planning review, then Budget validation.', 'images/help/ppmp/step-01-ppmp-index.png', 'PPMP status workflow'),
                    self::step('BAC consolidates all approved PPMPs into the APP.', 'images/help/app/step-01-app-overview.png', 'APP consolidation'),
                    self::step('Once Approved and Locked, items become available for Purchase Requests.', 'images/help/ppmp/step-01-ppmp-index.png', 'Approved PPMP'),
                ],
                'workflow' => ['Draft', 'Division Chief Review', 'Planning Review', 'Budget Validation', 'BAC Consolidation', 'Approved', 'Locked'],
                'errors' => ['"Exceeds allocated budget" — the division\'s total PPMP ABC is higher than its budget allocation; trim items or request more budget.'],
                'faqs' => [
                    'What if I need to add an item after Approval? Create a Supplemental or Amended PPMP; the original stays locked and auditable.',
                    'What is PPMP Type? Indicative (from pipeline) or Final (standalone).',
                ],
                'approval' => 'Draft → Division Chief → Planning → Budget → BAC Consolidation → Approved → Locked.',
                'tips' => 'Use "Return for Revision" instead of rejecting outright — it preserves the draft for the preparer to fix.',
            ],
            'purchase-request' => [
                'label' => 'Purchase Request (PR)',
                'purpose' => 'Formally request the procurement of specific PPMP line items, automatically deducting the PPMP balance to prevent double-utilization.',
                'steps' => [
                    self::step('Open Purchase Requests and click Create, or use the filter row to find existing PRs.', 'images/help/purchase-request/step-01-pr-index.png', 'Purchase Request list'),
                    self::step('Select Fiscal Year → Division → an Approved/Locked PPMP, then pick available items and quantities.', 'images/help/purchase-request/step-02-pr-create.png', 'PR creation form'),
                    self::step('Submit through Division Chief → Planning → Budget → HOPE approval.', 'images/help/purchase-request/step-03-pr-detail.png', 'PR detail — approval actions'),
                    self::step('Once Approved, generate the Certificate of Availability of Funds (CAF).', 'images/help/purchase-request/step-03-pr-detail.png', 'Generate CAF button on approved PR'),
                ],
                'workflow' => ['Draft', 'Division Chief', 'Planning', 'Budget', 'HOPE', 'Approved'],
                'errors' => ['"Item already fully utilized" — another PR already consumed the remaining PPMP quantity/budget for that item.'],
                'faqs' => ['Can a PR span multiple PPMP items? Yes, add as many line items as needed as long as each has remaining balance.'],
                'approval' => 'Draft → Division Chief → Planning → Budget → HOPE → Approved → CAF generated.',
                'tips' => 'Double-check quantities before submitting — Draft is the only editable state.',
            ],
            'caf' => [
                'label' => 'Certificate of Availability of Funds (CAF)',
                'purpose' => 'The Budget Officer\'s formal certification that funds exist for an approved Purchase Request, required before BAC processing can begin.',
                'steps' => [
                    self::step('Open the Approved PR and click Generate CAF, or find existing CAFs under Certificates of Availability of Funds.', 'images/help/caf/step-01-caf-index.png', 'CAF list'),
                    self::step('The system computes fund source, account code, amount, and remaining budget automatically.', 'images/help/caf/step-01-caf-index.png', 'CAF auto-computed fields'),
                    self::step('Certify, then approve; print the signed, QR-coded PDF.', 'images/help/caf/step-01-caf-index.png', 'Certify and print CAF'),
                ],
                'workflow' => ['Generated', 'Certified', 'Approved', 'Printed'],
                'errors' => ['QR code fails verification — the CAF may have been edited after printing; regenerate.'],
                'faqs' => ['Is one CAF issued per PR? Yes, one-to-one.'],
                'approval' => 'Generated → Certified by Budget Officer → Approved → Printed.',
                'tips' => 'Verify the printed CAF\'s QR code via Verify Document before releasing it to BAC.',
            ],
            'bac' => [
                'label' => 'BAC Procurement Case',
                'purpose' => 'Runs the full competitive bidding lifecycle for a certified Purchase Request: scheduling, PhilGEPS posting, bid opening, evaluation, post-qualification, and award.',
                'steps' => [
                    self::step('BAC Secretariat opens the procurement case (auto-created from a certified PR) from Procurements.', 'images/help/bac/step-01-procurements-index.png', 'Procurement cases list'),
                    self::step('Schedule key activities on the BAC Calendar — pre-bid conference, bid opening, evaluation, etc.', 'images/help/bac/step-02-bac-calendar.png', 'BAC Calendar'),
                    self::step('Post the opportunity to PhilGEPS (or upload manually). Manage BAC Members and TWG roster as needed.', 'images/help/philgeps/step-01-philgeps-index.png', 'PhilGEPS postings'),
                    self::step('Receive bidder clarifications, conduct bid opening, then evaluate bids in the Evaluation Matrix.', 'images/help/bac/step-01-procurements-index.png', 'Procurement case — Evaluation tab'),
                    self::step('Run post-qualification, issue Notice of Award, Notice to Proceed, then the Purchase Order.', 'images/help/bac/step-03-bac-members.png', 'BAC Members & TWG roster'),
                ],
                'workflow' => ['Planning', 'Posted', 'Bidding', 'Evaluation', 'Post-Qualification', 'Awarded', 'NTP Issued', 'PO Issued', 'Completed'],
                'errors' => ['"Cannot post to PhilGEPS" — a mode of procurement that does not require BAC/PhilGEPS posting was selected on the case.'],
                'faqs' => ['Who can answer a bidder clarification? BAC Chairperson, BAC Secretariat, or a BAC Member.'],
                'approval' => 'BAC Secretariat administers each stage; BAC Chairperson/HOPE approves Award and NTP issuance.',
                'tips' => 'Use the Calendar view to spot scheduling conflicts across simultaneous procurement cases.',
            ],
            'philgeps' => [
                'label' => 'PhilGEPS Posting',
                'purpose' => 'Publish (or record the manual posting of) a procurement opportunity on the government e-procurement portal.',
                'steps' => [
                    self::step('From the procurement case, click Post to PhilGEPS, or manage postings from PhilGEPS > Index.', 'images/help/philgeps/step-01-philgeps-index.png', 'PhilGEPS postings list'),
                    self::step('Enter the reference number, posting date, and closing date (or mark as a manual/offline posting if the API is unavailable).', 'images/help/philgeps/step-01-philgeps-index.png', 'Create / edit posting form'),
                    self::step('The posting auto-closes once the closing date passes, disabling late submissions.', 'images/help/philgeps/step-01-philgeps-index.png', 'Posting status: Published → Closed'),
                ],
                'workflow' => ['Published', 'Closed', 'Cancelled'],
                'errors' => ['Posting still shows "Published" past its closing date — the scheduled job runs every 15 minutes; wait or run it manually.'],
                'faqs' => ['Can I edit the closing date after publishing? Only before any bid has been submitted.'],
                'approval' => 'BAC Secretariat posts; no further approval required per RA 12009 IRR for standard postings.',
                'tips' => 'Always keep a manual-upload fallback ready in case the PhilGEPS API is down close to a deadline.',
            ],
            'bidder-portal' => [
                'label' => 'Bidder / Supplier Portal',
                'purpose' => 'A separate, self-service portal where suppliers register, maintain eligibility documents, browse opportunities, ask clarifications, submit bids, and track awards.',
                'steps' => [
                    self::step('Supplier registers at /bidder/register with business info and an account.', 'images/help/bidder-portal/step-02-bidder-register.png', 'Supplier registration form'),
                    self::step('Sign in at /bidder/login. Upload eligibility documents for BAC Secretariat verification.', 'images/help/bidder-portal/step-03-bidder-login.png', 'Supplier portal login'),
                    self::step('Agency staff verify suppliers under Bidders > (supplier) > Verify/Suspend.', 'images/help/bidder-portal/step-01-bidders-index.png', 'Agency-side bidders list'),
                    self::step('Verified suppliers browse Open Opportunities, order/download bidding documents, and ask clarifications.', 'images/help/bidder-portal/step-03-bidder-login.png', 'Supplier dashboard after login'),
                    self::step('Submit a bid electronically before the deadline; track award status on the Awards page.', 'images/help/bidder-portal/step-03-bidder-login.png', 'Bid submission & awards'),
                ],
                'workflow' => null,
                'errors' => ['"Bidding closed" when trying to submit — the closing date/time has already passed; late submission is disabled by design.'],
                'faqs' => ['How long does verification take? As soon as BAC Secretariat reviews the uploaded documents — usually within 1–2 business days.'],
                'approval' => 'BAC Secretariat/Chairperson verifies or suspends supplier accounts via Bidders > (supplier) > Verify/Suspend.',
                'tips' => 'Keep eligibility document expiry dates current — the system emails a reminder 30 days before expiry.',
            ],
            'purchase-order' => [
                'label' => 'Purchase Order, Delivery, Inspection, Acceptance, Payment',
                'purpose' => 'Issue the Purchase Order (directly for Small Value Procurement/Shopping/Direct Contracting, or after NTP for Public Bidding) and track through delivery, inspection, acceptance, and payment.',
                'steps' => [
                    self::step('Create a PO from an Approved PR (simplified modes) or from the procurement case after NTP (Public Bidding).', 'images/help/purchase-order/step-01-po-index.png', 'Purchase Orders list'),
                    self::step('Approve the PO and print the QR-coded PDF for the supplier.', 'images/help/purchase-order/step-01-po-index.png', 'PO approval & print'),
                    self::step('Record Delivery once goods/services arrive; conduct Inspection (must Pass before Acceptance).', 'images/help/purchase-order/step-01-po-index.png', 'PO lifecycle — Delivery & Inspection'),
                    self::step('Confirm Acceptance, then record Payment under Payment Monitoring.', 'images/help/purchase-order/step-02-payments-index.png', 'Payment Monitoring list'),
                ],
                'workflow' => ['Draft', 'Approved', 'Delivered', 'Inspected', 'Accepted', 'Invoiced', 'Paid'],
                'errors' => ['"Cannot accept" button missing — inspection must Pass before acceptance is allowed.'],
                'faqs' => ['Can a PO have multiple deliveries? Yes, partial deliveries are supported.'],
                'approval' => 'Supply Officer prepares → Division Chief/HOPE approves → Inspector inspects → end-user accepts → Cashier/Finance pays.',
                'tips' => 'Print the PO\'s QR-coded PDF for the supplier\'s copy immediately after approval.',
            ],
            'settings' => [
                'label' => 'System Settings',
                'purpose' => 'Central configuration for agency profile, fiscal years, master/reference data, procurement thresholds, approval routing, security policy, and integrations.',
                'steps' => [
                    self::step('Only Super Admin / System Admin can access Settings from the sidebar.', 'images/help/settings/step-01-settings-overview.png', 'Settings overview tabs'),
                    self::step('Agency Profile: name, logo, HOPE/BAC Chairperson, PhilGEPS org ID.', 'images/help/settings/step-01-settings-overview.png', 'Agency Profile tab'),
                    self::step('Fiscal Years: create and activate the current fiscal year.', 'images/help/gaa/step-01-fiscal-years.png', 'Fiscal Years tab'),
                    self::step('Reference Data & Thresholds: departments, divisions, UACS/PAP/fund codes, modes of procurement, thresholds, approval routing, holidays.', 'images/help/settings/step-02-reference-data.png', 'Reference Data tab with column filters'),
                    self::step('Security & Integrations: password policy, SMTP, SMS, PhilGEPS, AWS S3, backup schedule.', 'images/help/settings/step-01-settings-overview.png', 'Security & Integrations tabs'),
                ],
                'workflow' => null,
                'errors' => ['Changes not taking effect — some integration settings (mail/AWS) only apply to new requests.'],
                'faqs' => ['Where do I change procurement thresholds? Settings > Reference Data & Thresholds > Procurement Thresholds.'],
                'approval' => 'Restricted to settings.manage permission (Super Admin, System Admin by default).',
                'tips' => 'Review Approval Routing after every reorganization so the right role always receives the right approval step.',
            ],
            'reports' => [
                'label' => 'Reports & Analytics',
                'purpose' => 'Cross-module reporting (Budget, PPMP/APP, BAC, Suppliers, Purchases, Audit/COA) with Excel export for offline analysis and COA submission.',
                'steps' => [
                    self::step('Open Reports & Analytics from the sidebar.', 'images/help/reports/step-01-reports-overview.png', 'Reports overview tab'),
                    self::step('Pick a tab: Budget, PPMP & APP, BAC & PhilGEPS, Suppliers, Purchases & Payments, Audit & COA, or Analytics.', 'images/help/reports/step-01-reports-overview.png', 'Report category tabs'),
                    self::step('Optionally filter by fiscal year using the dropdown in the page header.', 'images/help/reports/step-01-reports-overview.png', 'Fiscal year filter'),
                    self::step('Click Export to Excel on any tab to download the underlying dataset.', 'images/help/reports/step-01-reports-overview.png', 'Export to Excel button'),
                ],
                'workflow' => null,
                'errors' => ['Export button missing — requires the reports.export permission.'],
                'faqs' => ['Can reports be scheduled/emailed? Not yet — export manually for now.'],
                'approval' => 'No approval required to view; requires reports.view / reports.export permissions.',
                'tips' => 'Use the Analytics tab\'s cycle-time and savings figures for COA performance reporting.',
            ],
        ];
    }

    public static function roleManuals(): array
    {
        return [
            'administrator' => [
                'label' => 'Administrator Manual',
                'summary' => 'Covers day-to-day platform administration: user accounts, roles, reference data, and monitoring.',
                'modules' => ['getting-started', 'settings', 'reports', 'gaa'],
            ],
            'system-administrator' => [
                'label' => 'System Administrator Manual',
                'summary' => 'Covers infrastructure-level configuration: AWS S3, SMTP/SMS integrations, backups, security policy, and audit retention.',
                'modules' => ['getting-started', 'settings'],
            ],
            'end-user' => [
                'label' => 'End User Manual',
                'summary' => 'For staff who prepare Project Proposals, PPMPs, and Purchase Requests within their division.',
                'modules' => ['getting-started', 'project-proposal', 'ppmp', 'purchase-request'],
            ],
            'budget-officer' => [
                'label' => 'Budget Officer Manual',
                'summary' => 'Covers GAA upload/validation, budget distribution, PR budget review, and CAF generation.',
                'modules' => ['getting-started', 'gaa', 'budget-allocation', 'purchase-request', 'caf'],
            ],
            'planning' => [
                'label' => 'Planning Manual',
                'summary' => 'Covers APP consolidation and the Planning-stage review of PPMPs and Purchase Requests.',
                'modules' => ['getting-started', 'app', 'project-proposal', 'ppmp', 'purchase-request'],
            ],
            'bac' => [
                'label' => 'BAC Manual',
                'summary' => 'Covers the full bidding lifecycle for BAC Chairperson, Secretariat, and Members.',
                'modules' => ['getting-started', 'bac', 'philgeps', 'purchase-order'],
            ],
            'supplier' => [
                'label' => 'Supplier Manual',
                'summary' => 'Guide for registered bidders using the separate Supplier Portal.',
                'modules' => ['bidder-portal'],
            ],
        ];
    }
}
