@props(['label', 'value', 'icon' => null, 'accent' => 'primary', 'sub' => null])
@php
    $accents = [
        'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300',
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'red' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'indigo' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    ];
@endphp
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $value }}</p>
            @if($sub)
                <p class="mt-1 text-xs text-slate-400">{{ $sub }}</p>
            @endif
        </div>
        @if($icon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $accents[$accent] ?? $accents['primary'] }}">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>
</div>
