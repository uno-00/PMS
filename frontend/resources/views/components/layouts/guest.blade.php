<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="$watch('dark', v => localStorage.setItem('dark', v))" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" href="data:,">
    <x-font-inter />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    @php
        $loginBackgroundUrl = \App\Models\Settings\AgencyProfile::current()->loginBackgroundUrl();
    @endphp

    <button @click="dark = !dark" class="fixed right-4 top-4 z-50 rounded-full border border-slate-200/80 bg-white/90 p-2.5 text-slate-600 shadow-lg shadow-slate-200/50 backdrop-blur transition hover:bg-white dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-300 dark:shadow-none" aria-label="Toggle dark mode">
        <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
        <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
    </button>

    <div class="flex min-h-screen flex-col lg:flex-row">
        {{-- Mobile hero --}}
        <div class="relative h-48 w-full shrink-0 overflow-hidden lg:hidden">
            @if ($loginBackgroundUrl)
                <img
                    src="{{ $loginBackgroundUrl }}"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover"
                    decoding="async"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/35 to-black/10" aria-hidden="true"></div>
            @else
                <div class="theme-gradient-panel absolute inset-0" aria-hidden="true"></div>
            @endif
            <div class="relative flex h-full flex-col justify-end p-5 text-white">
                <x-agency-brand variant="inverse" />
                <p class="mt-3 text-sm leading-relaxed text-white/85">Secure procurement portal for government agencies.</p>
            </div>
        </div>

        <x-login-brand-panel>
            <div class="flex items-center gap-3">
                <x-agency-brand variant="inverse" />
            </div>
            <div>
                <p class="text-sm font-medium uppercase tracking-widest text-white/70">Procurement Management System</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight text-white sm:text-4xl">Enterprise Procurement,<br>from GAA to Payment.</h1>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-white/90 sm:text-base">
                    A fully auditable, RA 12009-compliant procurement lifecycle platform for Philippine
                    government agencies &mdash; budget, planning, bidding, and contract management in one system.
                </p>
            </div>
            <p class="text-sm text-white/75">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </x-login-brand-panel>

        <div class="app-shell-bg flex w-full flex-1 items-center justify-center p-6 sm:p-8 lg:w-1/2">
            <div class="page-enter w-full max-w-md">
                <div class="surface-card-static p-8 sm:p-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
