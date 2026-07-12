<div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 lg:hidden dark:border-slate-800">
    <div class="flex gap-2">
        <button
            type="button"
            wire:click="focusSection({{ $index - 1 }})"
            class="flex-1 rounded-lg border border-slate-300 py-2.5 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200"
        >
            &larr; {{ $prev }}
        </button>
        @if($next)
            <button
                type="button"
                wire:click="focusSection({{ $index + 1 }})"
                class="flex-1 rounded-lg bg-primary-600 py-2.5 text-sm font-semibold text-white dark:bg-primary-500"
            >
                {{ $next }} &darr;
            </button>
        @endif
    </div>
</div>
