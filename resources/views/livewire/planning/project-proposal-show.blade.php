<div>
    <x-page-header :title="$projectProposal->title" :subtitle="'Control No. '.$projectProposal->control_no.' &middot; '.$projectProposal->document_ref">
        <x-slot:actions>
            <x-status-badge :status="$projectProposal->status" class="!text-sm" />
            @can('update', $projectProposal)
                <x-button href="{{ route('project-proposals.edit', $projectProposal) }}" variant="secondary">Edit</x-button>
            @endcan
            @if(($projectProposal->status === \App\Enums\ProjectProposalStatus::Draft || $projectProposal->status === \App\Enums\ProjectProposalStatus::ReturnedForRevision) && $projectProposal->isPipelineComplete())
                @can('submit', $projectProposal)
                    <x-button wire:click="submit">Submit for Recommendation</x-button>
                @endcan
            @endif
            @if($projectProposal->status === \App\Enums\ProjectProposalStatus::ForRecommendation)
                @can('recommend', $projectProposal)
                    <x-button wire:click="recommend" variant="success">Recommend</x-button>
                @endcan
                @can('approve', $projectProposal)
                    <x-button wire:click="approve" variant="success">Approve</x-button>
                @endcan
                @can('returnForRevision', $projectProposal)
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return for Revision</x-button>
                @endcan
            @endif
            <x-button href="{{ route('project-proposals.print', $projectProposal) }}" variant="secondary"><x-icon name="printer" class="h-4 w-4" /> Print</x-button>
        </x-slot:actions>
    </x-page-header>

    @error('approval')<div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>@enderror

    <x-pipeline-stepper :current-step="$projectProposal->pipeline_step ?? \App\Enums\ProjectProposalPipelineStep::ProjectProposal" class="mb-6" />

    @if(! $projectProposal->isPipelineComplete())
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
            Pipeline in progress. <a href="{{ \App\Support\ProjectProposalPipeline::continueRoute($projectProposal) }}" class="font-semibold underline">{{ \App\Support\ProjectProposalPipeline::continueLabel($projectProposal) }}</a>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Cost" :value="'₱'.number_format($projectProposal->total_cost, 2)" icon="banknotes" />
        <x-stat-card label="Fiscal Year" :value="$projectProposal->fiscalYear?->year ?? '—'" icon="calendar" accent="amber" />
        <x-stat-card label="Type of Project" :value="$projectProposal->project_type ?? '—'" icon="document-text" accent="indigo" />
    </div>

    @if($projectProposal->ppmp)
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            Indicative PPMP auto-generated: <a href="{{ route('ppmps.show', $projectProposal->ppmp) }}" class="font-medium underline">{{ $projectProposal->ppmp->control_no }}</a>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="I. Basic Information">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">Market Scoping</dt><dd class="mt-0.5"><a href="{{ route('market-scoping.show', $projectProposal->marketScoping) }}" class="text-primary-700 hover:underline dark:text-primary-400">{{ $projectProposal->marketScoping?->control_no }}</a></dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Schedule</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $projectProposal->schedule ?: '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Venue / Area</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $projectProposal->venue_area ?: '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Fund Source</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $projectProposal->fund_source_text ?: '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Proponent</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $projectProposal->proponent ?: '—' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Signatories">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">Prepared by</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $projectProposal->preparedBy?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Recommending Approval</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $projectProposal->recommendedBy?->name ?? '—' }} @if($projectProposal->recommended_at)<span class="text-slate-400">({{ $projectProposal->recommended_at->format('M d, Y') }})</span>@endif</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Approved</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $projectProposal->approvedBy?->name ?? '—' }} @if($projectProposal->approved_at)<span class="text-slate-400">({{ $projectProposal->approved_at->format('M d, Y') }})</span>@endif</dd></div>
            </dl>
        </x-card>
    </div>

    <div class="mt-6 space-y-6">
        <x-card title="II. Rationale"><x-rich-text-content :content="$projectProposal->rationale" /></x-card>
        <x-card title="III. Objectives"><x-rich-text-content :content="$projectProposal->objectives" /></x-card>
        <x-card title="IV. Target Schedule for the Project"><x-rich-text-content :content="$projectProposal->target_schedule" /></x-card>
        <x-card title="V. Budgetary Requirement"><x-rich-text-content :content="$projectProposal->budgetary_requirement" /></x-card>
        <x-card title="VI. Fund Source"><x-rich-text-content :content="$projectProposal->fund_source_narrative" /></x-card>
    </div>

    @if($showReturnModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Return for Revision</h3>
                <textarea wire:model="remarks" rows="3" class="mt-3 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-4 flex justify-end gap-2">
                    <x-button wire:click="$set('showReturnModal', false)" variant="secondary">Cancel</x-button>
                    <x-button wire:click="returnForRevision" variant="danger">Return</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
