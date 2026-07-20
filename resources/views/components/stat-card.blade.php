@props(['label', 'value', 'icon' => null, 'accent' => 'primary', 'sub' => null])
@php
    $accents = [
        'primary' => 'bg-primary-100 text-primary-700 ring-primary-200/60 group-hover:bg-primary-200/80 group-hover:ring-primary-300/60 dark:bg-primary-900/40 dark:text-primary-300 dark:ring-primary-800/50 dark:group-hover:bg-primary-900/60',
        'emerald' => 'bg-emerald-100 text-emerald-700 ring-emerald-200/60 group-hover:bg-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800/50 dark:group-hover:bg-emerald-900/60',
        'amber' => 'bg-amber-100 text-amber-700 ring-amber-200/60 group-hover:bg-amber-200/80 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800/50 dark:group-hover:bg-amber-900/60',
        'red' => 'bg-red-100 text-red-700 ring-red-200/60 group-hover:bg-red-200/80 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-800/50 dark:group-hover:bg-red-900/60',
        'indigo' => 'bg-indigo-100 text-indigo-700 ring-indigo-200/60 group-hover:bg-indigo-200/80 dark:bg-indigo-900/40 dark:text-indigo-300 dark:ring-indigo-800/50 dark:group-hover:bg-indigo-900/60',
    ];
@endphp
<div class="stat-card-interactive group relative overflow-hidden px-5 pt-5 pb-7 sm:px-6 sm:pt-6 sm:pb-8">
    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-primary-500/5 transition duration-300 group-hover:scale-110 group-hover:bg-primary-500/10 dark:bg-primary-400/5" aria-hidden="true"></div>
    <div class="relative flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 transition group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">{{ $label }}</p>
            <p class="mt-2 truncate text-2xl font-bold tracking-tight text-slate-900 transition group-hover:text-primary-800 dark:text-white dark:group-hover:text-primary-200 sm:text-3xl">{{ $value }}</p>
            @if($sub)
                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $sub }}</p>
            @endif
        </div>
        @if($icon)
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 transition-all duration-200 group-hover:scale-110 group-hover:shadow-sm {{ $accents[$accent] ?? $accents['primary'] }}">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>
</div>
