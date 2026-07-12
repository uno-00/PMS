<div>
    <x-page-header
        title="Step 3: Indicative PPMP"
        :subtitle="'Generate indicative PPMP from '.$projectProposal->control_no"
    >
        <x-slot:actions>
            <x-button href="{{ route('project-proposals.print', $projectProposal) }}" variant="secondary" size="sm" target="_blank">
                <x-icon name="printer" class="h-4 w-4" /> Project Proposal
            </x-button>
            @if($projectProposal->marketScoping)
                <x-button href="{{ route('market-scoping.print', $projectProposal->marketScoping) }}" variant="secondary" size="sm" target="_blank">
                    <x-icon name="printer" class="h-4 w-4" /> Market Scoping
                </x-button>
            @endif
            @if($projectProposal->ppmp)
                <x-button href="{{ route('ppmps.print', $projectProposal->ppmp) }}" variant="secondary" size="sm" target="_blank">
                    <x-icon name="printer" class="h-4 w-4" /> Indicative PPMP
                </x-button>
            @endif
            <x-button href="{{ route('project-proposals.wizard.market-scoping', $projectProposal) }}" variant="secondary" size="sm">&larr; Step 2</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-pipeline-stepper :current-step="$projectProposal->pipeline_step" />

    @error('generation')<div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>@enderror

    @if($projectProposal->ppmp)
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            Indicative PPMP generated: <a href="{{ route('ppmps.show', $projectProposal->ppmp) }}" class="font-semibold underline">{{ $projectProposal->ppmp->control_no }}</a>
            — pipeline complete. Print all documents below or open the PPMP record.
        </div>
    @endif

    <x-card title="Print Pipeline Documents" class="mb-6">
        <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
            Print the three pipeline documents. The Indicative PPMP print becomes available after generation.
        </p>
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
            <x-button href="{{ route('project-proposals.print', $projectProposal) }}" variant="secondary" target="_blank">
                <x-icon name="printer" class="h-4 w-4" /> Print Project Proposal ({{ $projectProposal->control_no }})
            </x-button>
            @if($projectProposal->marketScoping)
                <x-button href="{{ route('market-scoping.print', $projectProposal->marketScoping) }}" variant="secondary" target="_blank">
                    <x-icon name="printer" class="h-4 w-4" /> Print Market Scoping ({{ $projectProposal->marketScoping->control_no }})
                </x-button>
            @endif
            @if($projectProposal->ppmp)
                <x-button href="{{ route('ppmps.print', $projectProposal->ppmp) }}" variant="secondary" target="_blank">
                    <x-icon name="printer" class="h-4 w-4" /> Print Indicative PPMP ({{ $projectProposal->ppmp->control_no }})
                </x-button>
            @else
                <x-button variant="secondary" disabled title="Generate the Indicative PPMP first">
                    <x-icon name="printer" class="h-4 w-4" /> Print Indicative PPMP
                </x-button>
            @endif
        </div>
    </x-card>

    <x-card title="Indicative PPMP Template Preview">
        <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
            Review the data below. Click generate to create a draft Indicative PPMP linked to this project proposal and market scoping checklist.
        </p>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><dt class="text-xs uppercase text-slate-400">Document Type</dt><dd class="mt-0.5 font-medium">{{ $preview['document_type'] }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Fiscal Year</dt><dd class="mt-0.5 font-medium">{{ $preview['fiscal_year'] ?? '—' }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Division</dt><dd class="mt-0.5 font-medium">{{ $preview['division'] ?? '—' }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Total ABC</dt><dd class="mt-0.5 font-medium">₱{{ number_format($preview['total_abc'], 2) }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-400">PPMP Title</dt><dd class="mt-0.5 font-medium">{{ $preview['title'] }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Project Proposal Ref.</dt><dd class="mt-0.5 font-mono text-sm">{{ $preview['project_proposal_control_no'] }}</dd></div>
            <div><dt class="text-xs uppercase text-slate-400">Market Scoping Ref.</dt><dd class="mt-0.5 font-mono text-sm">{{ $preview['market_scoping_control_no'] ?? '—' }}</dd></div>
        </dl>

        <div class="mt-6 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="px-4 py-2">Item No.</th>
                        <th class="px-4 py-2">Item Name</th>
                        <th class="px-4 py-2">Unit</th>
                        <th class="px-4 py-2 text-right">Qty</th>
                        <th class="px-4 py-2 text-right">Unit Cost</th>
                        <th class="px-4 py-2 text-right">ABC</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-3">1</td>
                        <td class="px-4 py-3 font-medium">{{ $preview['item_name'] }}</td>
                        <td class="px-4 py-3">{{ $preview['unit'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $preview['quantity'] }}</td>
                        <td class="px-4 py-3 text-right">₱{{ number_format($preview['estimated_unit_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">₱{{ number_format($preview['total_abc'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($preview['description'] || $preview['specification'])
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                @if($preview['description'])
                    <div><dt class="text-xs uppercase text-slate-400">Description (from Rationale)</dt><dd class="mt-1"><x-rich-text-content :content="$preview['description']" /></dd></div>
                @endif
                @if($preview['specification'])
                    <div><dt class="text-xs uppercase text-slate-400">Specification (from Objectives)</dt><dd class="mt-1"><x-rich-text-content :content="$preview['specification']" /></dd></div>
                @endif
            </div>
        @endif
    </x-card>

    <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-between">
        <x-button href="{{ route('project-proposals.wizard.market-scoping', $projectProposal) }}" variant="secondary">&larr; Back to Market Scoping</x-button>
        <div class="flex flex-col gap-2 sm:flex-row">
            @if(! $projectProposal->ppmp)
                <x-button wire:click="generateIndicativePpmp">Generate Indicative PPMP</x-button>
            @else
                <x-button href="{{ route('project-proposals.show', $projectProposal) }}">View Project Proposal &rarr;</x-button>
                <x-button href="{{ route('ppmps.show', $projectProposal->ppmp) }}">Open Indicative PPMP &rarr;</x-button>
            @endif
        </div>
    </div>
</div>
