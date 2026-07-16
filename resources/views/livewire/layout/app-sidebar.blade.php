<nav class="space-y-6 px-3 py-5 text-sm" wire:key="app-sidebar-nav">
    <x-nav-group title="Overview">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard*')" icon="home">Dashboard</x-nav-link>
    </x-nav-group>

    @if($this->navCanAny(['gaa.view', 'budget-allocation.view', 'app.view', 'market-scoping.view', 'project-proposal.view']))
    <x-nav-group title="Budget & Planning">
        @if($this->navCan('gaa.view'))
            <x-nav-link :href="route('gaa.index')" :active="request()->routeIs('gaa.*')" icon="banknotes">General Appropriations Act</x-nav-link>
        @endif
        @if($this->navCan('app.view'))
            <x-nav-link :href="route('app.show')" :active="request()->routeIs('app.*')" icon="clipboard">Annual Procurement Plan</x-nav-link>
        @endif
        @if($this->navCan('budget-allocation.view'))
            <x-nav-link :href="route('budget-allocations.index')" :active="request()->routeIs('budget-allocations.*')" icon="chart-pie">Budget Allocation</x-nav-link>
        @endif
        @if($this->navCan('market-scoping.view'))
            <x-nav-link :href="route('market-scoping.index')" :active="request()->routeIs('market-scoping.*')" icon="clipboard">Market Scoping</x-nav-link>
        @endif
        @if($this->navCan('project-proposal.view'))
            <x-nav-link :href="route('project-proposals.index')" :active="request()->routeIs('project-proposals.*')" icon="document-text">Project Proposals</x-nav-link>
        @endif
    </x-nav-group>
    @endif

    @if($this->navCanAny(['ppmp.view', 'ppmp-consolidation.view']))
    <x-nav-group title="Planning">
        @if($this->navCan('ppmp.view'))
            <x-nav-link :href="route('ppmps.indicative')" :active="request()->routeIs('ppmps.indicative')" icon="document-text">Indicative PPMP</x-nav-link>
            <x-nav-link :href="route('ppmps.final')" :active="request()->routeIs('ppmps.final')" icon="document-text">Final PPMP</x-nav-link>
        @endif
        @if($this->navCan('ppmp-consolidation.view'))
            <x-nav-link :href="route('ppmp-consolidations.index')" :active="request()->routeIs('ppmp-consolidations.*')" icon="clipboard">PPMP Consolidation</x-nav-link>
        @endif
    </x-nav-group>
    @endif

    @if($this->navCanAny(['ppmp.view', 'purchase-request.view', 'caf.view']))
    <x-nav-group title="Procurement">
        @if($this->navCan('ppmp.view'))
            <x-nav-link :href="route('ppmps.index')" :active="request()->routeIs('ppmps.index') || request()->routeIs('ppmps.show') || request()->routeIs('ppmps.create') || request()->routeIs('ppmps.edit')" icon="document-text">All PPMP</x-nav-link>
        @endif
        @if($this->navCan('purchase-request.view'))
            <x-nav-link :href="route('purchase-requests.index')" :active="request()->routeIs('purchase-requests.*')" icon="shopping-cart">Purchase Requests</x-nav-link>
        @endif
        @if($this->navCan('caf.view'))
            <x-nav-link :href="route('cafs.index')" :active="request()->routeIs('cafs.*')" icon="check-badge">Certificate of Availability of Funds</x-nav-link>
        @endif
    </x-nav-group>
    @endif

    @if($this->navCanAny(['bac-calendar.view', 'bac-members.view', 'philgeps.view', 'bid-evaluation.view', 'award.view', 'ntp.view', 'bidder.view']))
    <x-nav-group title="BAC & Bidding">
        @if($this->navCan('bac-calendar.view'))
            <x-nav-link :href="route('procurements.index')" :active="request()->routeIs('procurements.*')" icon="briefcase">Procurement Cases</x-nav-link>
            <x-nav-link :href="route('bac-calendar.index')" :active="request()->routeIs('bac-calendar.*')" icon="calendar">BAC Calendar</x-nav-link>
        @endif
        @if($this->navCan('bac-members.view'))
            <x-nav-link :href="route('bac-members.index')" :active="request()->routeIs('bac-members.*')" icon="users">BAC Members & TWG</x-nav-link>
        @endif
        @if($this->navCan('philgeps.view'))
            <x-nav-link :href="route('philgeps.index')" :active="request()->routeIs('philgeps.*')" icon="globe">PhilGEPS Postings</x-nav-link>
        @endif
        @if($this->navCan('bidder.view'))
            <x-nav-link :href="route('bidders.index')" :active="request()->routeIs('bidders.*')" icon="building-office">Bidders / Suppliers</x-nav-link>
        @endif
    </x-nav-group>
    @endif

    @if($this->navCanAny(['purchase-order.view', 'payment.view']))
    <x-nav-group title="Award & Delivery">
        @if($this->navCan('purchase-order.view'))
            <x-nav-link :href="route('purchase-orders.index')" :active="request()->routeIs('purchase-orders.*')" icon="truck">Purchase Orders</x-nav-link>
        @endif
        @if($this->navCan('payment.view'))
            <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" icon="credit-card">Payments</x-nav-link>
        @endif
    </x-nav-group>
    @endif

    @if($this->navCanAny(['reports.view', 'audit-trail.view', 'access-settings', 'help.view']))
    <x-nav-group title="System">
        @if($this->navCan('reports.view'))
            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="chart-bar">Reports & Analytics</x-nav-link>
        @endif
        @if($this->navCan('audit-trail.view'))
            <x-nav-link :href="route('audit-trail.index')" :active="request()->routeIs('audit-trail.*')" icon="shield-check">Audit Trail</x-nav-link>
        @endif
        @if($this->navCan('access-settings'))
            <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" icon="cog">System Settings</x-nav-link>
        @endif
        @if($this->navCan('help.view'))
            <x-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')" icon="question-mark-circle">Help & User Manuals</x-nav-link>
        @endif
    </x-nav-group>
    @endif
</nav>
