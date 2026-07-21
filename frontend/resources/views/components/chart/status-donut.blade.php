@props([
    'title',
    'subtitle' => 'By pipeline stage',
    'items' => [],
    'centerLabel' => 'Total cases',
])

@php
    $rows = collect($items)
        ->filter(fn ($item) => (int) ($item['value'] ?? 0) > 0)
        ->values();

    $total = $rows->sum(fn ($item) => (int) ($item['value'] ?? 0));
    $active = $rows
        ->reject(fn ($item) => in_array($item['label'] ?? '', ['Completed', 'Cancelled'], true))
        ->sum(fn ($item) => (int) ($item['value'] ?? 0));

    $labels = $rows->pluck('label')->all();
    $data = $rows->pluck('value')->all();
    $colors = $rows->pluck('color')->all();
@endphp

<x-card :hover="false" {{ $attributes }}>
    <div class="mb-4 flex items-start justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
        <div>
            <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        </div>

        @if($total > 0)
            <span class="inline-flex shrink-0 items-center rounded-full border border-primary-200 bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 transition-all duration-200 hover:border-primary-300 hover:bg-primary-100 dark:border-primary-800/60 dark:bg-primary-950/40 dark:text-primary-300 dark:hover:border-primary-700">
                {{ number_format($active) }} active
            </span>
        @endif
    </div>

    @if($total > 0)
        <div class="relative h-52">
            <canvas
                x-data
                x-init="window.PmsCharts.statusDonut($el, {
                    labels: {{ Js::from($labels) }},
                    data: {{ Js::from($data) }},
                    colors: {{ Js::from($colors) }},
                    total: {{ $total }},
                    centerLabel: {{ Js::from($centerLabel) }},
                })"
            ></canvas>
        </div>

        <div class="mt-4 max-h-40 space-y-2 overflow-y-auto pb-1 pr-1 scrollbar-thin">
            @foreach($rows as $row)
                @php
                    $value = (int) ($row['value'] ?? 0);
                    $pct = $total > 0 ? round(($value / $total) * 100) : 0;
                @endphp
                <div class="flex items-center gap-3 rounded-xl bg-slate-50/80 px-3 py-2 transition-all duration-200 hover:-translate-y-px hover:bg-slate-100 hover:shadow-sm dark:bg-slate-800/40 dark:hover:bg-slate-800/70">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $row['color'] ?? '#94a3b8' }}"></span>
                    <span class="min-w-0 flex-1 truncate text-xs font-medium text-slate-700 dark:text-slate-200">{{ $row['label'] }}</span>
                    <span class="shrink-0 text-xs font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($value) }}</span>
                    <span class="w-10 shrink-0 text-right text-[11px] tabular-nums text-slate-400">{{ $pct }}%</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300/80 bg-slate-50/50 px-4 py-12 text-center dark:border-slate-700 dark:bg-slate-900/40">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200/80 dark:bg-slate-800 dark:ring-slate-700">
                <x-icon name="briefcase" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">No procurement cases yet</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Cases appear once PRs enter the BAC pipeline.</p>
        </div>
    @endif
</x-card>
