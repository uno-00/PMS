<?php

namespace Database\Seeders;

use App\Models\Settings\ApprovalRouting;
use App\Support\Roles;
use Illuminate\Database\Seeder;

/**
 * Default approval routing per document type. A Super Admin can re-point
 * any step to a different role from Settings > Approval Workflow without
 * touching code — the fixed Draft -> ... -> Approved sequence itself
 * comes from each module's status enum, this table only governs "who".
 */
class ApprovalRoutingSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            'gaa' => [
                ['validate', 'Validate Budget', Roles::BUDGET_OFFICER],
                ['approve', 'Approve GAA', Roles::BUDGET_OFFICER],
                ['distribute', 'Distribute Budget', Roles::BUDGET_OFFICER],
            ],
            'app' => [
                ['consolidation', 'Consolidate APP', Roles::PLANNING_OFFICER],
                ['bac_review', 'BAC Review', Roles::BAC_SECRETARIAT],
                ['approval', 'HOPE Approval', Roles::HOPE],
                ['lock', 'Lock APP', Roles::SYSTEM_ADMIN],
            ],
            'ppmp' => [
                ['division_chief_review', 'Division Chief Review', Roles::DIVISION_CHIEF],
                ['planning_review', 'Planning Review', Roles::PLANNING_OFFICER],
                ['budget_validation', 'Budget Validation', Roles::BUDGET_OFFICER],
                ['bac_consolidation', 'BAC Consolidation', Roles::BAC_SECRETARIAT],
                ['approval', 'Final Approval', Roles::HOPE],
            ],
            'purchase_request' => [
                ['division_chief', 'Division Chief Review', Roles::DIVISION_CHIEF],
                ['planning', 'Planning Review', Roles::PLANNING_OFFICER],
                ['budget', 'Budget Review', Roles::BUDGET_OFFICER],
                ['hope', 'HOPE Approval', Roles::HOPE],
            ],
            'caf' => [
                ['certify', 'Certify Availability of Funds', Roles::BUDGET_OFFICER],
                ['approve', 'Approve CAF', Roles::ACCOUNTING_OFFICER],
            ],
            'award' => [
                ['approve', 'Approve Notice of Award', Roles::BAC_CHAIRPERSON],
            ],
            'purchase_order' => [
                ['approve', 'Approve Purchase Order', Roles::HOPE],
            ],
        ];

        foreach ($routes as $documentType => $steps) {
            foreach ($steps as $sequence => [$stepKey, $label, $role]) {
                ApprovalRouting::query()->firstOrCreate(
                    ['document_type' => $documentType, 'step_key' => $stepKey],
                    ['step_label' => $label, 'sequence' => $sequence + 1, 'role_name' => $role]
                );
            }
        }
    }
}
