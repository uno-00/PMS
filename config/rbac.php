<?php

use App\Support\Permissions as P;
use App\Support\Roles;

/**
 * Default role -> permission matrix used by database/seeders/PermissionSeeder.php.
 *
 * This is seed data, not a runtime authorization source: once seeded, roles
 * and their permissions live in the database (Spatie Permission tables) and
 * can be edited by a Super Admin through Settings > Roles & Permissions
 * without touching code. Re-running the seeder is idempotent and will only
 * add newly introduced permissions to each role, it will not revoke
 * permissions an admin has since customized.
 */
return [

    'dashboards' => [
        Roles::SUPER_ADMIN => 'executive',
        Roles::SYSTEM_ADMIN => 'executive',
        Roles::BAC_CHAIRPERSON => 'bac',
        Roles::BAC_SECRETARIAT => 'bac',
        Roles::BAC_MEMBER => 'bac',
        Roles::BUDGET_OFFICER => 'budget',
        Roles::PLANNING_OFFICER => 'planning',
        Roles::ACCOUNTING_OFFICER => 'budget',
        Roles::SUPPLY_OFFICER => 'division',
        Roles::DIVISION_CHIEF => 'division',
        Roles::END_USER => 'division',
        Roles::HOPE => 'executive',
        Roles::INTERNAL_AUDITOR => 'analytics',
        Roles::INSPECTOR => 'division',
        Roles::PROPERTY_OFFICER => 'division',
        Roles::CASHIER => 'budget',
        Roles::FINANCE => 'budget',
        Roles::BIDDER => 'bidder.dashboard',
        Roles::VIEWER => 'analytics',
    ],

    'permissions' => [
        Roles::SUPER_ADMIN => ['*'],

        Roles::SYSTEM_ADMIN => array_merge(
            P::forModule('dashboard'),
            P::forModule('settings'),
            P::forModule('users'),
            P::forModule('fiscal-year'),
            P::forModule('audit-trail'),
            P::forModule('reports'),
            P::forModule('help'),
            P::forModule('bac-members'),
            ['bac-calendar.view']
        ),

        Roles::BAC_CHAIRPERSON => array_merge(
            P::forModule('dashboard'),
            ['bac-calendar.view', 'bac-calendar.manage'],
            P::forModule('bac-members'),
            ['app.view', 'app.review', 'app.lock', 'app.unlock'],
            ['philgeps.view'],
            ['bidder.view', 'bidder.verify', 'bidder.suspend'],
            ['bid-opening.view', 'bid-opening.conduct'],
            ['bid-evaluation.view'],
            ['clarification.view', 'clarification.answer'],
            ['post-qualification.view', 'post-qualification.approve'],
            ['award.view', 'award.generate', 'award.approve'],
            ['ntp.view', 'ntp.generate'],
            ['purchase-order.view'],
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::BAC_SECRETARIAT => array_merge(
            P::forModule('dashboard'),
            ['bac-calendar.view', 'bac-calendar.manage'],
            P::forModule('bac-members'),
            P::forModule('philgeps'),
            ['bidder.view', 'bidder.verify', 'bidder.suspend'],
            P::forModule('bid-documents'),
            P::forModule('bid-submission'),
            ['bid-opening.view', 'bid-opening.conduct'],
            ['bid-evaluation.view'],
            ['clarification.view', 'clarification.answer'],
            ['post-qualification.view', 'post-qualification.process'],
            ['award.view', 'award.generate'],
            ['ntp.view', 'ntp.generate'],
            ['purchase-order.view', 'purchase-order.create'],
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::BAC_MEMBER => array_merge(
            P::forModule('dashboard'),
            ['bac-calendar.view'],
            ['bidder.view'],
            ['bid-opening.view', 'bid-opening.conduct'],
            ['bid-evaluation.view', 'bid-evaluation.evaluate'],
            ['clarification.view'],
            ['post-qualification.view', 'post-qualification.process'],
            ['award.view'],
            ['reports.view'],
            P::forModule('help')
        ),

        Roles::BUDGET_OFFICER => array_merge(
            P::forModule('dashboard'),
            P::forModule('gaa'),
            ['app.view', 'app.review'],
            P::forModule('budget-allocation'),
            P::forModule('market-scoping'),
            ['ppmp.view', 'ppmp.validate-budget'],
            ['purchase-request.view', 'purchase-request.review-budget'],
            P::forModule('caf'),
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::PLANNING_OFFICER => array_merge(
            P::forModule('dashboard'),
            ['app.view', 'app.consolidate', 'app.review', 'app.lock', 'app.unlock'],
            P::forModule('market-scoping'),
            ['ppmp.view', 'ppmp.review-planning', 'ppmp.consolidate-bac'],
            ['purchase-request.view', 'purchase-request.review-planning'],
            ['bac-calendar.view'],
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::ACCOUNTING_OFFICER => array_merge(
            P::forModule('dashboard'),
            ['caf.view', 'caf.certify'],
            ['purchase-order.view'],
            P::forModule('payment'),
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::SUPPLY_OFFICER => array_merge(
            P::forModule('dashboard'),
            ['purchase-request.view', 'purchase-request.create', 'purchase-request.edit', 'purchase-request.submit'],
            ['purchase-order.view', 'purchase-order.create'],
            ['delivery.view', 'delivery.record'],
            ['inspection.view'],
            ['acceptance.view'],
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::DIVISION_CHIEF => array_merge(
            P::forModule('dashboard'),
            ['ppmp.view', 'ppmp.create', 'ppmp.edit', 'ppmp.submit', 'ppmp.review-division'],
            P::forModule('market-scoping'),
            ['purchase-request.view', 'purchase-request.create', 'purchase-request.edit', 'purchase-request.submit', 'purchase-request.review-division'],
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::END_USER => array_merge(
            P::forModule('dashboard'),
            ['ppmp.view'],
            ['market-scoping.view', 'market-scoping.create', 'market-scoping.edit'],
            ['purchase-request.view', 'purchase-request.create', 'purchase-request.edit', 'purchase-request.submit'],
            ['help.view']
        ),

        Roles::HOPE => array_merge(
            P::forModule('dashboard'),
            ['app.view', 'app.approve', 'app.lock', 'app.unlock'],
            ['ppmp.view', 'ppmp.approve'],
            ['purchase-request.view', 'purchase-request.approve-hope'],
            ['award.view', 'award.approve'],
            ['purchase-order.view', 'purchase-order.approve'],
            ['ntp.view'],
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::INTERNAL_AUDITOR => array_merge(
            P::forModule('dashboard'),
            ['audit-trail.view'],
            array_values(array_filter(P::all(), fn ($p) => str_ends_with($p, '.view'))),
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::INSPECTOR => array_merge(
            P::forModule('dashboard'),
            ['delivery.view'],
            ['inspection.view', 'inspection.conduct'],
            ['acceptance.view'],
            P::forModule('help')
        ),

        Roles::PROPERTY_OFFICER => array_merge(
            P::forModule('dashboard'),
            ['delivery.view', 'delivery.record'],
            ['inspection.view'],
            ['acceptance.view'],
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::CASHIER => array_merge(
            P::forModule('dashboard'),
            P::forModule('payment'),
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::FINANCE => array_merge(
            P::forModule('dashboard'),
            ['caf.view'],
            P::forModule('payment'),
            P::forModule('reports'),
            P::forModule('help')
        ),

        Roles::BIDDER => array_merge(
            ['dashboard.view'],
            ['philgeps.view'],
            ['bidder.view'],
            ['bid-documents.view'],
            ['bid-submission.view', 'bid-submission.submit'],
            ['clarification.view', 'clarification.ask'],
            ['award.view'],
            ['help.view']
        ),

        Roles::VIEWER => array_merge(
            P::forModule('dashboard'),
            array_values(array_filter(P::all(), fn ($p) => str_ends_with($p, '.view'))),
            P::forModule('help')
        ),
    ],
];
