@props(['title', 'subtitle' => null, 'fiscalYear' => null, 'role' => null])

@php
    $firstName = explode(' ', trim($title))[0] ?: $title;
    $initials = collect(explode(' ', trim($title)))->map(fn ($n) => $n[0] ?? '')->filter()->take(2)->join('');
    $hour = (int) now()->format('G');
    $greeting = match (true) {
        $hour < 12 => 'Good morning',
        $hour < 18 => 'Good afternoon',
        default => 'Good evening',
    };
@endphp

<div {{ $attributes->merge(['class' => 'surface-card-static group relative mb-6 overflow-hidden px-5 py-5 sm:px-6 sm:py-6']) }}>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-600 via-primary-500 to-emerald-500 opacity-90" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-primary-500/5 blur-3xl transition duration-500 group-hover:bg-primary-500/10 dark:bg-primary-400/5" aria-hidden="true"></div>

    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-600 to-primary-800 text-base font-bold text-white shadow-md shadow-primary-900/25 ring-4 ring-primary-500/10 transition duration-200 group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-primary-900/30 dark:ring-primary-400/10">
                {{ $initials }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-700 dark:text-primary-300">{{ $greeting }}</p>
                <h2 class="mt-0.5 truncate text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">{{ $firstName }}</h2>
                @if($subtitle)
                    <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            @if($role)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:border-slate-600">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                    </svg>
                    {{ $role }}
                </span>
            @endif
            @if($fiscalYear)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-primary-200 bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-800 transition hover:border-primary-300 hover:bg-primary-100 dark:border-primary-800/60 dark:bg-primary-950/50 dark:text-primary-200 dark:hover:border-primary-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    FY {{ $fiscalYear->year }}
                </span>
            @endif
        </div>
    </div>
</div>
