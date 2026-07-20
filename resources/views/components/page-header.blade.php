@props(['title', 'subtitle' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">{{ $title }}</h2>
        @if($subtitle)
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
