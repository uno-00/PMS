<?php

namespace App\Support;

/**
 * Single source of truth for the modules a Super Admin can activate or
 * deactivate from Settings > Module Management. Each entry carries the
 * human-facing metadata (label, description, the nav group it belongs to)
 * plus the kebab module key that every {module}.{action} permission in
 * Permissions.php is built from.
 *
 * Modules in $alwaysOn can be displayed but never toggled off (Settings
 * itself, plus dashboard/help which every user needs regardless of role).
 */
final class ModuleRegistry
{
    /**
     * @return array<int, array{key: string, label: string, description: string, group: string}>
     */
    public static function all(): array
    {
        return [
            // Budget & Planning
            ['key' => 'gaa', 'label' => 'General Appropriations Act', 'description' => 'Upload and distribute the annual budget from DBM.', 'group' => 'Budget & Planning'],
            ['key' => 'app', 'label' => 'Annual Procurement Plan', 'description' => 'Consolidate the APP from approved PPMPs per fiscal year.', 'group' => 'Budget & Planning'],
            ['key' => 'budget-allocation', 'label' => 'Budget Allocation', 'description' => 'Sub-allocate approved funds to divisions, offices, and cost centers.', 'group' => 'Budget & Planning'],
            ['key' => 'market-scoping', 'label' => 'Market Scoping', 'description' => 'Conduct and document pre-procurement market scoping.', 'group' => 'Budget & Planning'],
            ['key' => 'project-proposal', 'label' => 'Project Proposals', 'description' => 'Three-step indicative PPMP project proposal pipeline.', 'group' => 'Budget & Planning'],
            ['key' => 'ppmp-consolidation', 'label' => 'PPMP Consolidation', 'description' => 'Consolidate division PPMPs into agency-wide plans, BP2020, and WFP.', 'group' => 'Planning'],

            // Procurement
            ['key' => 'ppmp', 'label' => 'Project Procurement Management Plan', 'description' => 'Draft, review, and approve division PPMPs.', 'group' => 'Procurement'],
            ['key' => 'purchase-request', 'label' => 'Purchase Requests', 'description' => 'Create and route purchase requests for approval.', 'group' => 'Procurement'],
            ['key' => 'caf', 'label' => 'Certificate of Availability of Funds', 'description' => 'Generate and certify fund availability for each request.', 'group' => 'Procurement'],

            // BAC & Bidding
            ['key' => 'bac-calendar', 'label' => 'BAC Calendar', 'description' => 'Schedule and track BAC activities (conferences, openings, awards).', 'group' => 'BAC & Bidding'],
            ['key' => 'bac-members', 'label' => 'BAC Members & TWG', 'description' => 'Manage the BAC roster and Technical Working Group assignments.', 'group' => 'BAC & Bidding'],
            ['key' => 'philgeps', 'label' => 'PhilGEPS Postings', 'description' => 'Publish and monitor procurement cases on PhilGEPS.', 'group' => 'BAC & Bidding'],
            ['key' => 'bidder', 'label' => 'Bidders / Suppliers', 'description' => 'Manage registered suppliers and bidder verification.', 'group' => 'BAC & Bidding'],

            // Award & Delivery
            ['key' => 'purchase-order', 'label' => 'Purchase Orders', 'description' => 'Issue and track purchase orders to awarded suppliers.', 'group' => 'Award & Delivery'],
            ['key' => 'payment', 'label' => 'Payments', 'description' => 'Record and monitor disbursements against purchase orders.', 'group' => 'Award & Delivery'],

            // System (read-mostly, partially protected)
            ['key' => 'reports', 'label' => 'Reports & Analytics', 'description' => 'Standard and ad-hoc procurement reports.', 'group' => 'System'],
            ['key' => 'audit-trail', 'label' => 'Audit Trail', 'description' => 'Immutable record of every document and status change.', 'group' => 'System'],
        ];
    }

    /**
     * Modules that are shown for visibility but can never be deactivated.
     * These are foundational — disabling them would lock the admin out or
     * break core navigation.
     *
     * @return string[]
     */
    public static function alwaysOn(): array
    {
        return ['settings', 'dashboard', 'users', 'fiscal-year', 'help'];
    }

    /** @return string[] */
    public static function toggleableKeys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function isToggleable(string $key): bool
    {
        return in_array($key, self::toggleableKeys(), true);
    }

    /**
     * @return array<string, array<int, array{key: string, label: string, description: string}>>
     */
    public static function grouped(): array
    {
        return collect(self::all())->groupBy('group')->map(fn ($items) => $items->map(fn ($i) => [
            'key' => $i['key'],
            'label' => $i['label'],
            'description' => $i['description'],
        ])->all())->all();
    }
}
