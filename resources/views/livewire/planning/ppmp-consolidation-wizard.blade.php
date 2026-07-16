<div>
    <x-page-header
        :title="$consolidation?->title ?? 'New PPMP Consolidation'"
        :subtitle="$consolidation ? $consolidation->reference_no.' · v'.$consolidation->version_number : 'Wizard-based agency-wide PPMP consolidation'"
    >
        <x-slot:actions>
            @if($consolidation)
                <x-status-badge :status="$consolidation->status" class="!text-sm" />
                @can('export', $consolidation)
                    <x-button href="{{ route('ppmp-consolidations.print', $consolidation) }}" variant="secondary" target="_blank">Print PPMP</x-button>
                    <x-button href="{{ route('ppmp-consolidations.bp2020.print', $consolidation) }}" variant="secondary" target="_blank">Print BP2020</x-button>
                    <x-button href="{{ route('ppmp-consolidations.wfp.print', $consolidation) }}" variant="secondary" target="_blank">Print WFP</x-button>
                    <x-button href="{{ route('ppmp-consolidations.export', [$consolidation, 'excel']) }}" variant="secondary">Export Excel</x-button>
                @endcan
                @can('cancel', $consolidation)
                    <x-button wire:click="$set('showCancelModal', true)" variant="danger">Cancel Consolidation</x-button>
                @endcan
            @endif
            <x-button href="{{ route('ppmp-consolidations.index') }}" variant="secondary">Back to list</x-button>
        </x-slot:actions>
    </x-page-header>

    @if(session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif
    @if(session('warning'))
        <div class="mb-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">{{ session('warning') }}</div>
    @endif

    <x-consolidation-stepper :current-step="$step" />

    {{-- Step 1: Select PPMPs --}}
    @if($step === 1)
        <x-card title="Step 1 — Select PPMPs for Consolidation">
            <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Fiscal Year</label>
                    <select wire:model.live="fiscal_year_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" {{ ($consolidation && ! $consolidation->isEditable()) ? 'disabled' : '' }}>
                        <option value="">Select&hellip;</option>
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">PPMP Type</label>
                    <select wire:model.live="document_type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900" {{ ($consolidation && ! $consolidation->isEditable()) ? 'disabled' : '' }}>
                        @foreach($documentTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Division</label>
                    <select wire:model.live="filter_division_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <option value="">All divisions</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Search</label>
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="PPMP no. or title" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
            </div>

            @if($consolidation)
                <div class="mb-4">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Consolidation title</label>
                    <input wire:model="title" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
            @endif

            @if($warnings)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                    <p class="font-semibold">Active consolidation warnings</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($warnings as $warning)
                            <li>{{ $warning['ppmp']->control_no }} is already in {{ $warning['consolidation']->reference_no }} ({{ $warning['consolidation']->status->label() }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3 flex flex-wrap gap-2">
                <x-button type="button" wire:click="selectAllEligible" variant="secondary" size="sm">Select All</x-button>
                <x-button type="button" wire:click="unselectAll" variant="secondary" size="sm">Unselect All</x-button>
                <span class="self-center text-sm text-slate-500">{{ count($selectedPpmpIds) }} selected</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-2"></th>
                            <th class="py-2 pr-4">PPMP No.</th>
                            <th class="py-2 pr-4">Division</th>
                            <th class="py-2 pr-4 text-right">Budget</th>
                            <th class="py-2 pr-4">Items</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Approved</th>
                            <th class="py-2 pr-4">Version</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($eligiblePpmps as $ppmp)
                            <tr>
                                <td class="py-2 pr-2">
                                    <input type="checkbox" wire:model.live="selectedPpmpIds" value="{{ $ppmp->id }}" class="rounded border-slate-300">
                                </td>
                                <td class="py-2.5 pr-4 font-medium">{{ $ppmp->control_no ?? '—' }}</td>
                                <td class="py-2.5 pr-4">{{ $ppmp->division?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4 text-right">₱{{ number_format($ppmp->display_total ?? 0, 2) }}</td>
                                <td class="py-2.5 pr-4">{{ $ppmp->items_count }}</td>
                                <td class="py-2.5 pr-4"><x-status-badge :status="$ppmp->status" /></td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ $ppmp->approved_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="py-2.5 pr-4">{{ $ppmp->revision_number }}</td>
                                <td class="py-2.5">
                                    <a href="{{ route('ppmps.show', $ppmp) }}" class="text-primary-600 hover:underline" target="_blank">Preview</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="py-8 text-center text-slate-500">No eligible approved PPMPs for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <x-button wire:click="saveStep1" :disabled="count($selectedPpmpIds) === 0 || ! $fiscal_year_id">
                    Continue to Consolidated PPMP &rarr;
                </x-button>
            </div>
        </x-card>
    @endif

    {{-- Step 2: Consolidated PPMP --}}
    @if($step === 2 && $consolidation)
        <x-card title="Step 2 — Generate Consolidated PPMP">
            @if($consolidation->isEditable())
                <div class="mb-4 flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="mergeDuplicates" class="rounded border-slate-300">
                        Merge duplicate procurement items
                    </label>
                    <x-button wire:click="generateConsolidated" variant="secondary" size="sm">Regenerate / Recalculate</x-button>
                </div>
            @endif

            <p class="mb-4 text-sm text-slate-500">{{ $consolidation->sourcePpmps->count() }} source PPMP(s) · {{ $consolidation->items->count() }} consolidated line(s) · Total ₱{{ number_format($consolidation->total_budget, 2) }}</p>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">#</th>
                            <th class="py-2 pr-4">Item</th>
                            <th class="py-2 pr-4">Division</th>
                            <th class="py-2 pr-4">Qty</th>
                            <th class="py-2 pr-4">Mode</th>
                            <th class="py-2 pr-4">Fund</th>
                            <th class="py-2 pr-4 text-right">ABC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($consolidation->items as $item)
                            <tr class="{{ $item->is_merged ? 'bg-sky-50/50 dark:bg-sky-900/10' : '' }}">
                                <td class="py-2.5 pr-4">{{ $item->item_no }}</td>
                                <td class="py-2.5 pr-4">
                                    <p class="font-medium">{{ $item->item_name }}</p>
                                    @if($item->is_merged)
                                        <p class="text-xs text-sky-600">Merged from {{ count($item->source_ppmp_item_ids ?? []) }} source item(s)</p>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-4">{{ $item->division?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4">{{ rtrim(rtrim($item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                                <td class="py-2.5 pr-4">{{ $item->modeOfProcurement?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4">{{ $item->fundSource?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium">₱{{ number_format($item->lineAbc(), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-8 text-center text-slate-500">No consolidated items yet. Click Regenerate.</td></tr>
                        @endforelse
                    </tbody>
                    @if($consolidation->items->isNotEmpty())
                        <tfoot>
                            <tr class="font-bold">
                                <td colspan="6" class="py-2.5 pr-4 text-right">TOTAL</td>
                                <td class="py-2.5 pr-4 text-right">₱{{ number_format($consolidation->items->sum(fn ($i) => $i->lineAbc()), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div class="mt-6 flex justify-between">
                <x-button wire:click="goToStep(1)" variant="secondary">&larr; Back</x-button>
                @if($consolidation->isEditable() && $consolidation->items->isNotEmpty())
                    <x-button wire:click="generateBp2020">Continue to BP Form 2020 &rarr;</x-button>
                @endif
            </div>
        </x-card>
    @endif

    {{-- Step 3: BP Form 2020 --}}
    @if($step === 3 && $consolidation)
        <x-card title="Step 3 — BP Form 2020">
            @if($consolidation->isEditable())
                <x-button wire:click="generateBp2020" variant="secondary" size="sm" class="mb-4">Regenerate BP2020</x-button>
            @endif
            @include('livewire.planning.partials.consolidation-bp2020-table', ['lines' => $consolidation->bp2020Lines])
            <div class="mt-6 flex justify-between">
                <x-button wire:click="goToStep(2)" variant="secondary">&larr; Back</x-button>
                @if($consolidation->isEditable() && $consolidation->bp2020Lines->isNotEmpty())
                    <x-button wire:click="generateWfp">Continue to WFP &rarr;</x-button>
                @endif
            </div>
        </x-card>
    @endif

    {{-- Step 4: WFP --}}
    @if($step === 4 && $consolidation)
        <x-card title="Step 4 — Work and Financial Plan">
            @if($consolidation->isEditable())
                <x-button wire:click="generateWfp" variant="secondary" size="sm" class="mb-4">Regenerate WFP</x-button>
            @endif
            @include('livewire.planning.partials.consolidation-wfp-table', ['lines' => $consolidation->wfpLines])
            <div class="mt-6 flex justify-between">
                <x-button wire:click="goToStep(3)" variant="secondary">&larr; Back</x-button>
                @if($consolidation->isEditable() && $consolidation->wfpLines->isNotEmpty())
                    <x-button wire:click="runValidation">Continue to Review & Validation &rarr;</x-button>
                @endif
            </div>
        </x-card>
    @endif

    {{-- Step 5: Review & Validation --}}
    @if($step === 5 && $consolidation)
        <x-card title="Step 5 — Review and Validation">
            @php $issues = $consolidation->validation_issues ?? []; @endphp
            @if($issues === [])
                <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">All validation checks passed.</div>
            @else
                <div class="mb-4 space-y-2">
                    @foreach($issues as $issue)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm dark:border-amber-800 dark:bg-amber-900/20">
                            <span class="font-medium uppercase text-amber-700">{{ str_replace('_', ' ', $issue['type']) }}:</span>
                            {{ $issue['message'] }}
                            @if($issue['section'] === 'items' && $issue['record_id'])
                                <button type="button" wire:click="goToStep(2)" class="ml-2 text-primary-600 hover:underline">View item</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-800/50">
                    <p class="text-xs uppercase text-slate-400">Consolidated PPMP</p>
                    <p class="text-lg font-bold">₱{{ number_format($consolidation->items->sum(fn ($i) => $i->lineAbc()), 2) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-800/50">
                    <p class="text-xs uppercase text-slate-400">BP Form 2020</p>
                    <p class="text-lg font-bold">₱{{ number_format($consolidation->bp2020Lines->sum('budget_allocation'), 2) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-800/50">
                    <p class="text-xs uppercase text-slate-400">WFP</p>
                    <p class="text-lg font-bold">₱{{ number_format($consolidation->wfpLines->sum('budget_allocation'), 2) }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <x-button wire:click="goToStep(4)" variant="secondary">&larr; Back</x-button>
                @if($consolidation->isEditable())
                    <x-button wire:click="runValidation" variant="secondary" class="mr-2">Re-run Validation</x-button>
                    @can('submit', $consolidation)
                        <x-button wire:click="submitForApproval" :disabled="count($issues) > 0">Submit for Approval &rarr;</x-button>
                    @endcan
                @endif
            </div>
        </x-card>
    @endif

    {{-- Step 6: Approval Workflow --}}
    @if($step === 6 && $consolidation)
        <x-card title="Step 6 — Approval Workflow">
            <div class="mb-4">
                <x-workflow-timeline :history="$consolidation->workflowHistories" />
            </div>

            <textarea wire:model="remarks" rows="2" placeholder="Remarks (optional for approval, required for return)" class="mb-4 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900"></textarea>

            <div class="flex flex-wrap gap-2">
                @can('planningReview', $consolidation)
                    <x-button wire:click="planningApprove" variant="success">Approve (Planning)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return</x-button>
                @endcan
                @can('budgetReview', $consolidation)
                    <x-button wire:click="budgetApprove" variant="success">Approve (Budget)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return</x-button>
                @endcan
                @can('accountingReview', $consolidation)
                    <x-button wire:click="accountingApprove" variant="success">Approve (Accounting)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return</x-button>
                @endcan
                @can('bacReview', $consolidation)
                    <x-button wire:click="bacApprove" variant="success">Approve (BAC)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return</x-button>
                @endcan
                @can('hopeReview', $consolidation)
                    <x-button wire:click="hopeApprove" variant="success">Final Approve (HoPE)</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return</x-button>
                @endcan
            </div>

            @if(in_array($consolidation->status->value, ['planning_review','budget_review','accounting_review','bac_review','hope_review','approved','locked']))
                <div class="mt-6">
                    <x-button wire:click="goToStep(7)">View Final Consolidation &rarr;</x-button>
                </div>
            @endif
        </x-card>
    @endif

    {{-- Step 7: Final Consolidation --}}
    @if($step === 7 && $consolidation)
        <x-card title="Step 7 — Final Consolidation">
            <p class="mb-4 text-sm text-slate-500">Approved consolidated documents are ready for release and locking.</p>

            <div class="mb-6 flex flex-wrap gap-2">
                <x-button href="{{ route('ppmp-consolidations.print', $consolidation) }}" variant="secondary" target="_blank">Final Consolidated PPMP (PDF)</x-button>
                <x-button href="{{ route('ppmp-consolidations.bp2020.print', $consolidation) }}" variant="secondary" target="_blank">Final BP Form 2020 (PDF)</x-button>
                <x-button href="{{ route('ppmp-consolidations.wfp.print', $consolidation) }}" variant="secondary" target="_blank">Final WFP (PDF)</x-button>
                <x-button href="{{ route('ppmp-consolidations.export', [$consolidation, 'excel']) }}" variant="secondary">Export Excel</x-button>
            </div>

            @can('lock', $consolidation)
                <x-button wire:click="lock" variant="success">Lock Final Documents</x-button>
            @endcan

            @if($consolidation->isLocked())
                <p class="mt-4 text-sm text-emerald-700 dark:text-emerald-300">Locked on {{ $consolidation->locked_at?->format('F d, Y g:ia') }}.</p>
            @endif
        </x-card>
    @endif

    @if($showReturnModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold">Return for Revision</h3>
                <textarea wire:model="remarks" rows="3" class="mt-3 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-4 flex justify-end gap-2">
                    <x-button wire:click="$set('showReturnModal', false)" variant="secondary">Cancel</x-button>
                    <x-button wire:click="returnForRevision" variant="danger">Return</x-button>
                </div>
            </div>
        </div>
    @endif

    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-red-700 dark:text-red-400">Cancel Consolidation</h3>
                <p class="mt-1 text-sm text-slate-500">This will cancel the consolidation and release all source PPMPs for selection again.</p>
                <textarea wire:model="remarks" rows="3" placeholder="Reason for cancellation (required)" class="mt-3 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-4 flex justify-end gap-2">
                    <x-button wire:click="$set('showCancelModal', false)" variant="secondary">Close</x-button>
                    <x-button wire:click="cancel" variant="danger" wire:confirm="Cancel this PPMP consolidation?">Confirm Cancel</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
