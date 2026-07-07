<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('dark') === 'true', sidebarOpen: false }" x-init="$watch('dark', v => localStorage.setItem('dark', v))" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} &middot; {{ config('app.name') }}</title>
    <link rel="icon" href="data:,">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full transform overflow-y-auto border-r border-slate-200 bg-white transition-transform duration-200 ease-in-out dark:border-slate-800 dark:bg-slate-900 lg:static lg:translate-x-0"
            :class="{ '!translate-x-0': sidebarOpen }"
        >
            <div class="flex h-16 items-center border-b border-slate-200 px-5 dark:border-slate-800">
                <x-agency-brand :href="route('dashboard')" class="w-full" />
            </div>

            <nav class="space-y-6 px-3 py-5 text-sm">
                <x-nav-group title="Overview">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard*')" icon="home">Dashboard</x-nav-link>
                </x-nav-group>

                @canany(['gaa.view', 'budget-allocation.view', 'app.view', 'market-scoping.view'])
                <x-nav-group title="Budget &amp; Planning">
                    @can('gaa.view')
                        <x-nav-link :href="route('gaa.index')" :active="request()->routeIs('gaa.*')" icon="banknotes">General Appropriations Act</x-nav-link>
                    @endcan
                    @can('app.view')
                        <x-nav-link :href="route('app.show')" :active="request()->routeIs('app.*')" icon="clipboard">Annual Procurement Plan</x-nav-link>
                    @endcan
                    @can('budget-allocation.view')
                        <x-nav-link :href="route('budget-allocations.index')" :active="request()->routeIs('budget-allocations.*')" icon="chart-pie">Budget Allocation</x-nav-link>
                    @endcan
                    @can('market-scoping.view')
                        <x-nav-link :href="route('market-scoping.index')" :active="request()->routeIs('market-scoping.*')" icon="clipboard">Market Scoping</x-nav-link>
                    @endcan
                </x-nav-group>
                @endcanany

                @canany(['ppmp.view', 'purchase-request.view', 'caf.view'])
                <x-nav-group title="Procurement">
                    @can('ppmp.view')
                        <x-nav-link :href="route('ppmps.index')" :active="request()->routeIs('ppmps.*')" icon="document-text">PPMP</x-nav-link>
                    @endcan
                    @can('purchase-request.view')
                        <x-nav-link :href="route('purchase-requests.index')" :active="request()->routeIs('purchase-requests.*')" icon="shopping-cart">Purchase Requests</x-nav-link>
                    @endcan
                    @can('caf.view')
                        <x-nav-link :href="route('cafs.index')" :active="request()->routeIs('cafs.*')" icon="check-badge">Certificate of Availability of Funds</x-nav-link>
                    @endcan
                </x-nav-group>
                @endcanany

                @canany(['bac-calendar.view', 'bac-members.view', 'philgeps.view', 'bid-evaluation.view', 'award.view', 'ntp.view'])
                <x-nav-group title="BAC &amp; Bidding">
                    @can('bac-calendar.view')
                        <x-nav-link :href="route('procurements.index')" :active="request()->routeIs('procurements.*')" icon="briefcase">Procurement Cases</x-nav-link>
                        <x-nav-link :href="route('bac-calendar.index')" :active="request()->routeIs('bac-calendar.*')" icon="calendar">BAC Calendar</x-nav-link>
                    @endcan
                    @can('bac-members.view')
                        <x-nav-link :href="route('bac-members.index')" :active="request()->routeIs('bac-members.*')" icon="users">BAC Members &amp; TWG</x-nav-link>
                    @endcan
                    @can('philgeps.view')
                        <x-nav-link :href="route('philgeps.index')" :active="request()->routeIs('philgeps.*')" icon="globe">PhilGEPS Postings</x-nav-link>
                    @endcan
                    @can('bidder.view')
                        <x-nav-link :href="route('bidders.index')" :active="request()->routeIs('bidders.*')" icon="building-office">Bidders / Suppliers</x-nav-link>
                    @endcan
                </x-nav-group>
                @endcanany

                @canany(['purchase-order.view', 'payment.view'])
                <x-nav-group title="Award &amp; Delivery">
                    @can('purchase-order.view')
                        <x-nav-link :href="route('purchase-orders.index')" :active="request()->routeIs('purchase-orders.*')" icon="truck">Purchase Orders</x-nav-link>
                    @endcan
                    @can('payment.view')
                        <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" icon="credit-card">Payments</x-nav-link>
                    @endcan
                </x-nav-group>
                @endcanany

                @canany(['reports.view', 'audit-trail.view', 'access-settings', 'help.view'])
                <x-nav-group title="System">
                    @can('reports.view')
                        <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="chart-bar">Reports &amp; Analytics</x-nav-link>
                    @endcan
                    @can('audit-trail.view')
                        <x-nav-link :href="route('audit-trail.index')" :active="request()->routeIs('audit-trail.*')" icon="shield-check">Audit Trail</x-nav-link>
                    @endcan
                    @can('access-settings')
                        <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" icon="cog">System Settings</x-nav-link>
                    @endcan
                    @can('help.view')
                        <x-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')" icon="question-mark-circle">Help &amp; User Manuals</x-nav-link>
                    @endcan
                </x-nav-group>
                @endcanany
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 sm:px-6">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="shrink-0 rounded-md p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <x-agency-brand compact :href="route('dashboard')" class="min-w-0 lg:hidden" />
                    <h1 class="hidden truncate text-base font-semibold text-slate-800 dark:text-slate-100 lg:block">{{ $title ?? 'Dashboard' }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        @if(session('impersonating_fiscal_year'))
                            <span class="hidden rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 sm:inline">FY {{ session('impersonating_fiscal_year') }}</span>
                        @endif
                    @endauth

                    <button @click="dark = !dark" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </button>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-700 text-xs font-semibold text-white">
                                {{ collect(explode(' ', auth()->user()->name ?? 'U'))->map(fn($n) => $n[0] ?? '')->take(2)->join('') }}
                            </span>
                            <span class="hidden text-sm font-medium text-slate-700 dark:text-slate-200 sm:inline">{{ auth()->user()->name ?? 'Guest' }}</span>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-56 rounded-lg border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-900">
                            <div class="border-b border-slate-100 px-4 py-2 dark:border-slate-800">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400">{{ auth()->user()->primaryRoleName() }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div class="mx-4 mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 sm:mx-6">
                    {{ session('status') }}
                </div>
            @endif

            <main class="flex-1 px-4 py-6 sm:px-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
