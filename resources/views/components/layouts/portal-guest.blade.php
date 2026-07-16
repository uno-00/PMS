<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('dark') === 'true' }" x-init="$watch('dark', v => localStorage.setItem('dark', v))" :class="{ 'dark': dark }">
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
    <div class="flex min-h-screen">
        <x-login-brand-panel>
            <div class="flex items-center gap-3">
                <x-agency-brand variant="inverse" subtitle="Supplier / Bidder Portal" />
            </div>
            <div>
                <h1 class="text-3xl font-bold leading-tight">Do business with<br>government, transparently.</h1>
                <p class="mt-4 max-w-md text-white/80">
                    Register your company, browse open opportunities, purchase bidding documents, submit bids
                    electronically, and track your awards &mdash; all in one place.
                </p>
            </div>
            <p class="text-sm text-white/70">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </x-login-brand-panel>
        <div class="flex w-full flex-1 items-center justify-center p-6 lg:w-1/2">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
