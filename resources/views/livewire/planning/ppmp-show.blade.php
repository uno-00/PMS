<div>
    <x-page-header :title="$ppmp->title" :subtitle="'Control No. '.$ppmp->control_no.' &middot; '.$ppmp->division?->name">
        <x-slot:actions>
            <x-status-badge :status="$ppmp->status" class="!text-sm" />
            @can('update', $ppmp)
                <x-button href="{{ route('ppmps.edit', $ppmp) }}" variant="secondary">Edit</x-button>
            @endcan
            @if($ppmp->status === \App\Enums\PpmpStatus::Draft || $ppmp->status === \App\Enums\PpmpStatus::ReturnedForRevision)
                @can('submit', $ppmp)
                    <x-button wire:click="submit">Submit for Review</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::DivisionChiefReview)
                @can('divisionChiefReview', $ppmp)
                    <x-button wire:click="divisionChiefApprove" variant="success">Endorse (Division Chief)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return for Revision</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::PlanningReview)
                @can('planningReview', $ppmp)
                    <x-button wire:click="planningApprove" variant="success">Endorse (Planning)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return for Revision</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::BacConsolidation)
                @can('bacConsolidate', $ppmp)
                    <x-button wire:click="bacConsolidate" variant="success">Consolidate (BAC Secretariat)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return for Revision</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::ProcurementModeReview)
                @can('procurementModeReview', $ppmp)
                    <x-button wire:click="applySuggestedModes" variant="secondary">Apply Suggested Modes</x-button>
                    <x-button wire:click="recommendProcurementModes" variant="success">Recommend Procurement Mode</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return for Revision</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::BudgetValidation)
                @can('budgetValidate', $ppmp)
                    <x-button wire:click="budgetOfficerApprove" variant="success">Support Budget Linkage</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return for Revision</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::Approved && ! $ppmp->approved_by)
                @can('approve', $ppmp)
                    <x-button wire:click="approve" variant="success">Final Approve (Commit Budget)</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::Approved && $ppmp->approved_by)
                @can('lock', $ppmp)
                    <x-button wire:click="lock" variant="secondary">Lock</x-button>
                    <x-button wire:click="createRevision('supplemental')" variant="secondary">Create Supplemental</x-button>
                    <x-button wire:click="createRevision('amended')" variant="secondary">Create Amendment</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::Locked)
                <x-button href="{{ route('ppmps.print', $ppmp) }}" variant="secondary" target="_blank">
                    <x-icon name="printer" class="h-4 w-4" /> Print PPMP
                </x-button>
                @if($ppmp->marketScoping)
                    <x-button href="{{ route('market-scoping.print', $ppmp->marketScoping) }}" variant="secondary" target="_blank">
                        <x-icon name="printer" class="h-4 w-4" /> Print Market Scoping
                    </x-button>
                @endif
                @if($ppmp->projectProposal)
                    <x-button href="{{ route('project-proposals.print', $ppmp->projectProposal) }}" variant="secondary" target="_blank">
                        <x-icon name="printer" class="h-4 w-4" /> Print Project Proposal
                    </x-button>
                @endif
            @else
                <x-button href="{{ route('ppmps.print', $ppmp) }}" variant="secondary"><x-icon name="printer" class="h-4 w-4" /> Print</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @error('budget')<div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>@enderror
    @error('procurement')<div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>@enderror

    @if($ppmp->projectProposal)
        <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800 dark:border-sky-800 dark:bg-sky-900/20 dark:text-sky-300">
            Pipeline: <a href="{{ route('project-proposals.show', $ppmp->projectProposal) }}" class="underline">Project Proposal</a>
            @if($ppmp->marketScoping)
                → <a href="{{ route('market-scoping.show', $ppmp->marketScoping) }}" class="underline">Market Scoping</a>
            @endif
            → Indicative PPMP
        </div>
    @endif

    @if($ppmp->status === \App\Enums\PpmpStatus::Locked)
        <x-card title="Print Pipeline Documents" class="mb-6">
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                This PPMP is locked for the fiscal year. Print the complete indicative pipeline document set below.
            </p>
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <x-button href="{{ route('ppmps.print', $ppmp) }}" variant="secondary" target="_blank">
                    <x-icon name="printer" class="h-4 w-4" /> Print PPMP ({{ $ppmp->control_no }})
                </x-button>
                @if($ppmp->marketScoping)
                    <x-button href="{{ route('market-scoping.print', $ppmp->marketScoping) }}" variant="secondary" target="_blank">
                        <x-icon name="printer" class="h-4 w-4" /> Print Market Scoping ({{ $ppmp->marketScoping->control_no }})
                    </x-button>
                @endif
                @if($ppmp->projectProposal)
                    <x-button href="{{ route('project-proposals.print', $ppmp->projectProposal) }}" variant="secondary" target="_blank">
                        <x-icon name="printer" class="h-4 w-4" /> Print Project Proposal ({{ $ppmp->projectProposal->control_no }})
                    </x-button>
                @endif
            </div>
        </x-card>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total ABC" :value="'₱'.number_format($totalLineAbc, 2)" icon="banknotes" />
        <x-stat-card label="Items" :value="$items->count()" icon="document-text" accent="indigo" />
        <x-stat-card label="Fiscal Year" :value="$ppmp->fiscalYear?->year" icon="calendar" accent="amber" />
        <x-stat-card label="Document Type" :value="$ppmp->document_type?->label() ?? 'Indicative'" icon="document-text" accent="sky" />
    </div>

    @if($ppmp->status === \App\Enums\PpmpStatus::ProcurementModeReview)
        @can('procurementModeReview', $ppmp)
            <x-card title="BAC: Recommend Mode of Procurement" class="mt-6">
                <div class="space-y-3">
                    @foreach($items as $index => $item)
                        <div class="flex flex-col gap-2 rounded-lg bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between dark:bg-slate-900/50">
                            <div>
                                <p class="font-medium text-slate-700 dark:text-slate-200">{{ $item->item_name }}</p>
                                <p class="text-xs text-slate-500">ABC: ₱{{ number_format($item->lineAbc(), 2) }}</p>
                            </div>
                            <select wire:model="itemModes.{{ $index }}.mode_of_procurement_id" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">Select mode</option>
                                @foreach($modes as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endcan
    @endif

    @if($ppmp->status === \App\Enums\PpmpStatus::BudgetValidation)
        @can('budgetValidate', $ppmp)
            <x-card title="Budget Officer: Budget Linkage" class="mt-6">
                <div class="space-y-3">
                    @foreach($items as $index => $item)
                        <div class="flex flex-col gap-2 rounded-lg bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between dark:bg-slate-900/50">
                            <div>
                                <p class="font-medium text-slate-700 dark:text-slate-200">{{ $item->item_name }}</p>
                                <p class="text-xs text-slate-500">Mode: {{ $item->modeOfProcurement?->name ?? '—' }} · ABC: ₱{{ number_format($item->lineAbc(), 2) }}</p>
                            </div>
                            <select wire:model="itemBudgets.{{ $index }}.budget_allocation_id" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">Select budget allocation</option>
                                @foreach($allocations as $allocation)
                                    <option value="{{ $allocation->id }}">{{ $allocation->pap?->name }} — ₱{{ number_format($allocation->remaining_balance, 2) }} remaining</option>
                                @endforeach
                            </select>
                            @if($allocations->isEmpty())
                                <p class="text-xs text-amber-600 dark:text-amber-400">No budget allocations found for this division and fiscal year. Ask the Budget Officer to distribute the GAA or run the demo seeder.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endcan
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Procurement Items" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Item</th>
                            <th class="py-2 pr-4">Qty/Unit</th>
                            <th class="py-2 pr-4">Mode</th>
                            <th class="py-2 pr-4">Fund Source</th>
                            <th class="py-2 pr-4 text-right">ABC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($items as $item)
                            <tr>
                                <td class="py-2.5 pr-4">
                                    <p class="font-medium text-slate-700 dark:text-slate-200">{{ $item->item_name }}</p>
                                    @if($item->plainDescription())
                                        <p class="text-xs text-slate-400">{{ $item->plainDescription() }}</p>
                                    @endif
                                    @if($item->plainSpecification())
                                        <p class="text-xs text-slate-400">{{ $item->plainSpecification() }}</p>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-4">{{ rtrim(rtrim($item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ $item->modeOfProcurement?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ $item->fundSource?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium">₱{{ number_format($item->lineAbc(), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Workflow History">
            <x-workflow-timeline :history="$history" />
        </x-card>
    </div>

    @if($showReturnModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Return for Revision</h3>
                <p class="mt-1 text-sm text-slate-500">Please provide remarks explaining what needs to be revised.</p>
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
