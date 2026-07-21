@props(['clear' => 'resetFilters'])

<div {{ $attributes->merge(['class' => 'mb-3 flex flex-wrap items-center justify-between gap-2']) }}>
    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $slot }}</p>
    <x-button size="sm" variant="ghost" wire:click="{{ $clear }}">Clear Filters</x-button>
</div>
