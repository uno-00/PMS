<?php

namespace App\Support;

/**
 * Defines every permission in the system as a {module}.{action} matrix.
 * PermissionSeeder reads this to create Spatie permissions, and role
 * mappings (config/rbac.php) reference these same strings, so the whole
 * authorization surface is generated from one source of truth instead of
 * being hand-typed in dozens of places.
 */
final class Permissions
{
    /**
     * @return array<string, array<int, string>> module => [actions]
     */
    public static function matrix(): array
    {
        return [
            'dashboard' => ['view', 'view-executive', 'view-analytics'],
            'settings' => ['view', 'manage'],
            'users' => ['view', 'create', 'edit', 'delete', 'manage-roles'],
            'fiscal-year' => ['view', 'create', 'edit', 'activate'],
            'gaa' => ['view', 'upload', 'validate', 'approve', 'distribute', 'delete'],
            'app' => ['view', 'consolidate', 'review', 'approve', 'lock', 'unlock'],
            'budget-allocation' => ['view', 'allocate', 'reallocate', 'edit', 'delete'],
            'market-scoping' => ['view', 'create', 'edit', 'approve', 'delete'],
            'project-proposal' => ['view', 'create', 'edit', 'submit', 'recommend', 'approve', 'delete'],
            'ppmp' => ['view', 'create', 'edit', 'submit', 'review-division', 'review-planning', 'validate-budget', 'consolidate-bac', 'recommend-procurement-mode', 'approve', 'lock', 'delete'],
            'ppmp-consolidation' => ['view', 'create', 'edit', 'submit', 'review-planning', 'review-budget', 'review-accounting', 'review-bac', 'approve-hope', 'lock', 'export', 'cancel', 'delete'],
            'purchase-request' => ['view', 'create', 'edit', 'submit', 'review-division', 'review-planning', 'review-budget', 'approve-hope', 'delete'],
            'caf' => ['view', 'generate', 'certify', 'approve', 'print'],
            'bac-calendar' => ['view', 'create', 'edit', 'delete', 'manage'],
            'bac-members' => ['view', 'create', 'edit', 'delete'],
            'philgeps' => ['view', 'create', 'edit', 'delete', 'post', 'manage', 'upload-manual'],
            'bidder' => ['view', 'verify', 'suspend', 'manage'],
            'bid-documents' => ['view', 'sell', 'manage'],
            'bid-submission' => ['view', 'submit', 'manage'],
            'clarification' => ['view', 'ask', 'answer'],
            'bid-opening' => ['view', 'conduct'],
            'bid-evaluation' => ['view', 'evaluate'],
            'post-qualification' => ['view', 'process', 'approve'],
            'award' => ['view', 'generate', 'approve'],
            'ntp' => ['view', 'generate'],
            'purchase-order' => ['view', 'create', 'approve'],
            'delivery' => ['view', 'record'],
            'inspection' => ['view', 'conduct'],
            'acceptance' => ['view', 'confirm'],
            'payment' => ['view', 'create', 'edit', 'delete', 'process', 'release'],
            'reports' => ['view', 'export'],
            'audit-trail' => ['view'],
            'help' => ['view'],
        ];
    }

    /** @return array<int, string> */
    public static function all(): array
    {
        $permissions = [];
        foreach (self::matrix() as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }

    public static function forModule(string $module): array
    {
        return array_map(
            fn (string $action) => "{$module}.{$action}",
            self::matrix()[$module] ?? []
        );
    }
}
