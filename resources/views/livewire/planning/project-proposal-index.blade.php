<div>
    <x-page-header title="Indicative PPMP Pipeline" subtitle="Three-step procedure: Project Proposal → Market Scoping → Indicative PPMP generation.">
        <x-slot:actions>
            @can('create', \App\Models\Planning\ProjectProposal::class)
                <x-button href="{{ route('project-proposals.create') }}"><x-icon name="plus" class="h-4 w-4" /> Start New Pipeline</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card class="mb-6">
        <x-pipeline-stepper :current-step="\App\Enums\ProjectProposalPipelineStep::ProjectProposal" />
        <p class="text-sm text-slate-500 dark:text-slate-400">Click <strong>Start New Pipeline</strong> to begin Step 1. Each record tracks progress through all three steps.</p>
    </x-card>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search project proposal records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Control No.</th>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Division</th>
                        <th class="py-2 pr-4">Pipeline Step</th>
                        <th class="py-2 pr-4 text-right">Total Cost</th>
                        <th class="py-2 pr-4">PPMP</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterControlNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterTitle" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterDivisionId">
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterPipelineStep">
                                @foreach(\App\Enums\ProjectProposalPipelineStep::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterTotalCost" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterPpmp" placeholder="PPMP no…" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($records as $record)
                        <tr wire:key="proposal-{{ $record->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $record->control_no }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $record->title }}</td>
                            <td class="py-3 pr-4">{{ $record->division?->name }}</td>
                            <td class="py-3 pr-4">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Step {{ ($record->pipeline_step ?? \App\Enums\ProjectProposalPipelineStep::ProjectProposal)->number() }}: {{ ($record->pipeline_step ?? \App\Enums\ProjectProposalPipelineStep::ProjectProposal)->label() }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($record->total_cost, 2) }}</td>
                            <td class="py-3 pr-4">
                                @if($record->ppmp)
                                    <a href="{{ route('ppmps.show', $record->ppmp) }}" class="text-primary-700 hover:underline dark:text-primary-400">{{ $record->ppmp->control_no }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $record->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right">
                                <x-button href="{{ \App\Support\ProjectProposalPipeline::continueRoute($record) }}" variant="secondary" size="sm">
                                    {{ \App\Support\ProjectProposalPipeline::continueLabel($record) }}
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="mt-4">{{ $records->links() }}</div>
        @endif
    </x-card>
</div>
