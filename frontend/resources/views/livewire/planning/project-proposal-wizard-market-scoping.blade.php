@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header
        title="Step 2: Market Scoping"
        :subtitle="'Linked to Project Proposal '.$projectProposal->control_no.' — '.$projectProposal->title"
    >
        <x-slot:actions>
            <x-button href="{{ route('project-proposals.edit', $projectProposal) }}" variant="secondary" size="sm">&larr; Step 1</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-pipeline-stepper :current-step="\App\Enums\ProjectProposalPipelineStep::MarketScoping" />

    <form class="space-y-5 sm:space-y-6">
        @include('livewire.planning.partials.market-scoping-form-body')

        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between">
            <x-button href="{{ route('project-proposals.edit', $projectProposal) }}" variant="secondary">&larr; Back to Project Proposal</x-button>
            <div class="flex gap-2">
                <x-button type="button" wire:click="save" variant="secondary">Save Draft</x-button>
                <x-button type="button" wire:click="saveAndContinue">Next: Indicative PPMP &rarr;</x-button>
            </div>
        </div>
    </form>
</div>
