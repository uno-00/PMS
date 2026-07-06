<?php

namespace App\Support;

/**
 * Static content backing the Help & User Manuals module (Settings-free —
 * this is documentation, not configuration). Split into per-module
 * guides (Purpose / Steps / Screenshots / Workflow / Common Errors /
 * FAQs / Approval Process / Tips, as required) and per-role manuals
 * that point a new user at the modules relevant to their job.
 */
final class HelpContent
{
    public static function modules(): array
    {
        return [
            'gaa' => [
                'label' => 'General Appropriations Act (GAA)',
                'purpose' => 'Record the fiscal year\'s DBM-issued General Appropriations Act and validate it before any budget can be allocated.',
                'steps' => [
                    'Super Admin creates the Fiscal Year in Settings > Fiscal Years.',
                    'Budget Officer opens GAA > Upload and selects the fiscal year.',
                    'Upload the DBM Excel template; the system validates line items and flags mismatches.',
                    'Review the Budget Summary and Budget Comparison screens.',
                    'Submit for validation, then approval.',
                    'Once approved, distribute the GAA to generate the initial Budget Allocation tree.',
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
                    'An APP record is auto-created once the GAA for the fiscal year is approved.',
                    'Divisions submit PPMPs which roll up into the APP for consolidation.',
                    'BAC Secretariat reviews the consolidated plan.',
                    'HOPE/BAC approves; the APP is then locked for the rest of the fiscal year.',
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
                    'Open Budget > Allocations.',
                    'Select the parent node (e.g. the whole GAA) and click Allocate.',
                    'Choose the target department/division/cost center and enter the amount — the system rejects any amount exceeding the parent\'s remaining balance.',
                    'Repeat until the fiscal year\'s budget is fully distributed.',
                ],
                'workflow' => null,
                'errors' => ['"Amount exceeds remaining balance" — the parent allocation doesn\'t have enough unallocated funds left.'],
                'faqs' => ['Can I reallocate later? Yes, reduce the source allocation first, then increase the destination.'],
                'approval' => 'Budget Officer allocates directly; every allocation is logged to the Audit Trail.',
                'tips' => 'Leave a small contingency unallocated at the top level for mid-year adjustments.',
            ],
            'ppmp' => [
                'label' => 'Project Procurement Management Plan (PPMP)',
                'purpose' => 'Each division\'s itemized procurement plan for the fiscal year — the sole source from which Purchase Requests may be created.',
                'steps' => [
                    'Division staff drafts the PPMP and adds items (description, specification, unit, quantity, ABC, schedule, mode of procurement, fund source, PAP, UACS).',
                    'Submit for Division Chief review, then Planning review, then Budget validation.',
                    'BAC consolidates all approved PPMPs into the APP.',
                    'Once Approved and Locked, items become available for Purchase Requests.',
                ],
                'workflow' => ['Draft', 'Division Chief Review', 'Planning Review', 'Budget Validation', 'BAC Consolidation', 'Approved', 'Locked'],
                'errors' => ['"Exceeds allocated budget" — the division\'s total PPMP ABC is higher than its budget allocation; trim items or request more budget.'],
                'faqs' => ['What if I need to add an item after Approval? Create a Supplemental or Amended PPMP; the original stays locked and auditable.'],
                'approval' => 'Draft → Division Chief → Planning → Budget → BAC Consolidation → Approved → Locked.',
                'tips' => 'Use "Return for Revision" instead of rejecting outright — it preserves the draft for the preparer to fix.',
            ],
            'purchase-request' => [
                'label' => 'Purchase Request (PR)',
                'purpose' => 'Formally request the procurement of specific PPMP line items, automatically deducting the PPMP balance to prevent double-utilization.',
                'steps' => [
                    'Select Fiscal Year → Division → an Approved/Locked PPMP.',
                    'Pick available (not-yet-utilized) items and quantities.',
                    'Submit through Division Chief → Planning → Budget → HOPE approval.',
                    'Once Approved, generate the Certificate of Availability of Funds (CAF).',
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
                    'Open the Approved PR and click Generate CAF.',
                    'The system computes fund source, account code, amount, and remaining budget automatically.',
                    'Certify, then approve; print the signed, QR-coded PDF.',
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
                    'BAC Secretariat opens the procurement case (auto-created from a certified PR) and schedules key activities on the BAC Calendar.',
                    'Post the opportunity to PhilGEPS (or upload manually).',
                    'Receive bidder clarifications and answer them before the deadline.',
                    'Conduct bid opening, then evaluate bids in the Evaluation Matrix.',
                    'Run post-qualification (document validation, site visit).',
                    'Issue Notice of Award, then Notice to Proceed, then the Purchase Order.',
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
                    'From the procurement case, click "Post to PhilGEPS."',
                    'Enter the reference number, posting date, and closing date (or mark as a manual/offline posting if the API is unavailable).',
                    'The posting auto-closes once the closing date passes, disabling late submissions.',
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
                    'Supplier registers at /bidder/register with business info and an account.',
                    'Upload eligibility documents (PhilGEPS registration, Mayor\'s Permit, Tax Clearance, SEC/DTI, PCAB) for BAC Secretariat verification.',
                    'Browse Open Opportunities, order/download bidding documents, ask clarifications.',
                    'Submit a bid electronically before the deadline (submissions lock automatically afterward).',
                    'Track award status on the Awards page.',
                ],
                'workflow' => null,
                'errors' => ['"Bidding closed" when trying to submit — the closing date/time has already passed; late submission is disabled by design.'],
                'faqs' => ['How long does verification take? As soon as BAC Secretariat reviews the uploaded documents — usually within 1–2 business days.'],
                'approval' => 'BAC Secretariat/Chairperson verifies or suspends supplier accounts via Bidders > (supplier) > Verify/Suspend.',
                'tips' => 'Keep eligibility document expiry dates current — the system emails a reminder 30 days before expiry.',
            ],
            'purchase-order' => [
                'label' => 'Purchase Order, Delivery, Inspection, Acceptance, Payment',
                'purpose' => 'Issue the Purchase Order (directly for Small Value Procurement/Shopping/Direct Contracting, or after NTP for Public Bidding) and track it through delivery, inspection, acceptance, and payment.',
                'steps' => [
                    'Create a PO from an Approved PR (simplified modes) or from the procurement case after NTP (Public Bidding).',
                    'Approve the PO.',
                    'Record Delivery once goods/services arrive.',
                    'Conduct Inspection; only a Passed result allows Acceptance.',
                    'Confirm Acceptance, then record Payment (OR No., amount, method).',
                ],
                'workflow' => ['Draft', 'Approved', 'Delivered', 'Inspected', 'Accepted', 'Invoiced', 'Paid'],
                'errors' => ['"Cannot accept" button missing — inspection must Pass before acceptance is allowed.'],
                'faqs' => ['Can a PO have multiple deliveries? Yes, partial deliveries are supported.'],
                'approval' => 'Supply Officer prepares → Division Chief/HOPE approves → Inspector inspects → end-user accepts → Cashier/Finance pays.',
                'tips' => 'Print the PO\'s QR-coded PDF for the supplier\'s copy immediately after approval.',
            ],
            'settings' => [
                'label' => 'System Settings',
                'purpose' => 'Central configuration for agency profile, fiscal years, master/reference data, procurement thresholds, approval routing, security policy, and integrations — nothing procurement-critical is hardcoded.',
                'steps' => [
                    'Only Super Admin / System Admin can access Settings.',
                    'Agency Profile: name, logo, HOPE/BAC Chairperson, PhilGEPS org ID.',
                    'Fiscal Years: create and activate the current fiscal year.',
                    'Reference Data & Thresholds: departments, divisions, cost centers, UACS/PAP/fund codes, modes of procurement, procurement thresholds, approval routing, holiday calendar.',
                    'Security: password policy, session timeout, audit log retention.',
                    'Integrations: SMTP, SMS, PhilGEPS, AWS S3, database backup schedule.',
                ],
                'workflow' => null,
                'errors' => ['Changes not taking effect — some integration settings (mail/AWS) only apply to new requests; give the queue/next request a moment.'],
                'faqs' => ['Where do I change procurement thresholds? Settings > Reference Data & Thresholds > Procurement Thresholds.'],
                'approval' => 'Restricted to settings.manage permission (Super Admin, System Admin by default).',
                'tips' => 'Review Approval Routing after every reorganization so the right role always receives the right approval step.',
            ],
            'reports' => [
                'label' => 'Reports & Analytics',
                'purpose' => 'Cross-module reporting (Budget, PPMP/APP, BAC, Suppliers, Purchases, Audit/COA) with Excel export for offline analysis and COA submission.',
                'steps' => [
                    'Open Reports & Analytics and pick a tab.',
                    'Optionally filter by fiscal year.',
                    'Click "Export to Excel" on any tab to download the underlying dataset.',
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
                'modules' => ['settings', 'reports', 'gaa'],
            ],
            'system-administrator' => [
                'label' => 'System Administrator Manual',
                'summary' => 'Covers infrastructure-level configuration: AWS S3, SMTP/SMS integrations, backups, security policy, and audit retention.',
                'modules' => ['settings'],
            ],
            'end-user' => [
                'label' => 'End User Manual',
                'summary' => 'For staff who prepare PPMPs and Purchase Requests within their division.',
                'modules' => ['ppmp', 'purchase-request'],
            ],
            'budget-officer' => [
                'label' => 'Budget Officer Manual',
                'summary' => 'Covers GAA upload/validation, budget distribution, PR budget review, and CAF generation.',
                'modules' => ['gaa', 'budget-allocation', 'purchase-request', 'caf'],
            ],
            'planning' => [
                'label' => 'Planning Manual',
                'summary' => 'Covers APP consolidation and the Planning-stage review of PPMPs and Purchase Requests.',
                'modules' => ['app', 'ppmp', 'purchase-request'],
            ],
            'bac' => [
                'label' => 'BAC Manual',
                'summary' => 'Covers the full bidding lifecycle for BAC Chairperson, Secretariat, and Members.',
                'modules' => ['bac', 'philgeps', 'purchase-order'],
            ],
            'supplier' => [
                'label' => 'Supplier Manual',
                'summary' => 'Guide for registered bidders using the separate Supplier Portal.',
                'modules' => ['bidder-portal'],
            ],
        ];
    }
}
