<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('dark') === 'true', menuOpen: false }" x-init="$watch('dark', v => localStorage.setItem('dark', v))" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Supplier / Bidder Portal' }} &middot; {{ config('app.name') }}</title>
    <link rel="icon" href="data:,">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('bidder.dashboard') }}" class="min-w-0">
                    <x-agency-brand subtitle="Supplier / Bidder Portal" />
                </a>
                <nav class="hidden items-center gap-1 text-sm md:flex">
                    <a href="{{ route('bidder.dashboard') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('bidder.dashboard') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Dashboard</a>
                    <a href="{{ route('bidder.opportunities.index') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('bidder.opportunities.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Opportunities</a>
                    <a href="{{ route('bidder.orders.index') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('bidder.orders.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Bid Doc Orders</a>
                    <a href="{{ route('bidder.bids.index') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('bidder.bids.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">My Bids</a>
                    <a href="{{ route('bidder.awards.index') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('bidder.awards.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">Awards</a>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <button @click="dark = !dark" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-700 text-xs font-semibold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'B', 0, 1)) }}</span>
                        <span class="hidden text-sm font-medium text-slate-700 dark:text-slate-200 sm:inline">{{ auth()->user()->name ?? 'Bidder' }}</span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 rounded-lg border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-900">
                        <a href="{{ route('bidder.profile') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">Company Profile</a>
                        <a href="{{ route('help.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">Help</a>
                        <form method="POST" action="{{ route('bidder.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <nav class="flex items-center gap-1 overflow-x-auto border-t border-slate-100 px-4 py-2 text-xs dark:border-slate-800 md:hidden">
            <a href="{{ route('bidder.dashboard') }}" class="whitespace-nowrap rounded-md px-2.5 py-1.5 font-medium text-slate-600 dark:text-slate-300">Dashboard</a>
            <a href="{{ route('bidder.opportunities.index') }}" class="whitespace-nowrap rounded-md px-2.5 py-1.5 font-medium text-slate-600 dark:text-slate-300">Opportunities</a>
            <a href="{{ route('bidder.orders.index') }}" class="whitespace-nowrap rounded-md px-2.5 py-1.5 font-medium text-slate-600 dark:text-slate-300">Orders</a>
            <a href="{{ route('bidder.bids.index') }}" class="whitespace-nowrap rounded-md px-2.5 py-1.5 font-medium text-slate-600 dark:text-slate-300">My Bids</a>
            <a href="{{ route('bidder.awards.index') }}" class="whitespace-nowrap rounded-md px-2.5 py-1.5 font-medium text-slate-600 dark:text-slate-300">Awards</a>
        </nav>
    </header>

    @if (session('status'))
        <div class="mx-auto mt-4 max-w-7xl rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 sm:px-6">
            {{ session('status') }}
        </div>
    @endif

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
