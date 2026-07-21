@props([
    'planned' => [],
    'actual' => [],
    'onTrack' => null,
    'subtitle' => 'In millions PHP · rolling 12 months',
])

<x-card :hover="false" {{ $attributes }}>
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">Planned vs Actual Spend</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        </div>

        @if($onTrack === true)
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:border-emerald-700">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0V11.25" />
                </svg>
                On track
            </span>
        @elseif($onTrack === false)
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 transition-all duration-200 hover:border-amber-300 hover:bg-amber-100 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300 dark:hover:border-amber-700">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25" />
                </svg>
                Over plan
            </span>
        @endif
    </div>

    <div class="h-72 pb-1">
        <canvas
            x-data
            x-init="window.PmsCharts.spendTracker($el, { planned: {{ Js::from($planned) }}, actual: {{ Js::from($actual) }} })"
        ></canvas>
    </div>
</x-card>
