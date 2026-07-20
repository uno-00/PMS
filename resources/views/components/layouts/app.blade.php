<!DOCTYPE html>
<html lang="en" class="h-full" x-data="{ dark: localStorage.getItem('dark') === 'true', sidebarOpen: false }" x-init="$watch('dark', v => localStorage.setItem('dark', v))" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} &middot; {{ config('app.name') }}</title>
    <link rel="icon" href="data:,">
    <x-font-inter />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
    @livewireStyles
</head>
<body class="h-full overflow-hidden bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="flex h-full flex-col">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Mobile sidebar drawer --}}
        <aside
            class="scrollbar-thin fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transform overflow-y-auto border-r border-slate-200 bg-white shadow-xl transition-transform duration-300 ease-out dark:border-slate-800 dark:bg-slate-900 lg:hidden"
            :class="{ '!translate-x-0': sidebarOpen }"
        >
            <div class="flex h-16 items-center border-b border-slate-200 px-5 dark:border-slate-800">
                <x-agency-brand :href="route('dashboard')" class="w-full" />
            </div>
            <livewire:layout.app-sidebar />
        </aside>

        {{-- Top bar — stays fixed; only the pane below scrolls --}}
        <header class="z-30 flex h-16 shrink-0 items-stretch border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="hidden w-72 shrink-0 items-center border-r border-slate-200 px-5 dark:border-slate-800 lg:flex">
                <x-agency-brand :href="route('dashboard')" class="w-full min-w-0" />
            </div>

            <div class="flex min-w-0 flex-1 items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="icon-btn shrink-0 lg:hidden" aria-label="Toggle navigation">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <x-agency-brand compact :href="route('dashboard')" class="min-w-0 lg:hidden" />
                    <div class="hidden min-w-0 lg:block">
                        <h1 class="truncate text-sm font-semibold leading-tight text-slate-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="truncate text-xs leading-tight text-slate-500 dark:text-slate-400">{{ auth()->user()->primaryRoleName() }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        @if(session('impersonating_fiscal_year'))
                            <span class="hidden rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:border-primary-800 dark:bg-primary-900/40 dark:text-primary-300 sm:inline">FY {{ session('impersonating_fiscal_year') }}</span>
                        @endif
                    @endauth

                    <button @click="dark = !dark" class="icon-btn" aria-label="Toggle dark mode">
                        <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </button>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-full border border-transparent py-1 pl-1 pr-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-200 hover:bg-slate-100 hover:shadow-sm dark:hover:border-slate-700 dark:hover:bg-slate-800">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-primary-600 to-primary-800 text-xs font-bold text-white shadow-sm ring-2 ring-white dark:ring-slate-900">
                                {{ collect(explode(' ', auth()->user()->name ?? 'U'))->map(fn($n) => $n[0] ?? '')->take(2)->join('') }}
                            </span>
                            <span class="hidden text-sm font-medium text-slate-700 dark:text-slate-200 sm:inline">{{ auth()->user()->name ?? 'Guest' }}</span>
                            <svg class="hidden h-4 w-4 text-slate-400 sm:block" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl shadow-slate-200/50 dark:border-slate-700 dark:bg-slate-900 dark:shadow-none">
                            <div class="border-b border-slate-100 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                                <p class="mt-1 text-xs font-medium text-primary-700 dark:text-primary-300">{{ auth()->user()->primaryRoleName() }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 transition-all duration-200 hover:bg-red-50 hover:pl-5 dark:hover:bg-red-950/30">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- App shell: sidebar + scrollable main pane --}}
        <div class="flex min-h-0 flex-1">
            <aside class="scrollbar-thin hidden w-72 shrink-0 overflow-y-auto border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 lg:block">
                <livewire:layout.app-sidebar />
            </aside>

            <div class="app-shell-bg scrollbar-thin flex min-h-0 min-w-0 flex-1 flex-col overflow-y-auto">
                @if (session('status'))
                    <div class="flash-banner-success mx-4 mt-4 sm:mx-6" role="status">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <main class="page-enter min-h-full flex-1 px-4 pt-6 pb-12 sm:px-6 sm:pb-14 lg:pt-8 lg:pb-16">
                    <div class="mb-5 lg:hidden">
                        <h1 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->primaryRoleName() }}</p>
                    </div>

                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
