@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $section = 'text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500';
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header
        :title="$marketScoping ? 'Edit Market Scoping' : 'New Market Scoping'"
        subtitle="Complete the NGPA Market Scoping Checklist for PPMP development."
    >
        @if($marketScoping)
            <x-slot:actions>
                <x-button href="{{ route('market-scoping.show', $marketScoping) }}" variant="secondary" size="sm">Back</x-button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <form wire:submit="save" class="space-y-5 sm:space-y-6">
        @include('livewire.planning.partials.market-scoping-form-body')

        <div class="flex justify-end gap-2">
            @if($marketScoping)
                <x-button href="{{ route('market-scoping.show', $marketScoping) }}" variant="secondary">Cancel</x-button>
            @endif
            <x-button type="submit">Save Checklist</x-button>
        </div>
    </form>
</div>
