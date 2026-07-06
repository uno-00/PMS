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
            @if($ppmp->status === \App\Enums\PpmpStatus::BudgetValidation)
                @can('budgetValidate', $ppmp)
                    <x-button wire:click="budgetOfficerApprove" variant="success">Validate Budget</x-button>
                    <x-button wire:click="$set('showReturnModal', true)" variant="danger">Return for Revision</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::BacConsolidation)
                @can('bacConsolidate', $ppmp)
                    <x-button wire:click="bacConsolidate" variant="secondary">Consolidate (BAC)</x-button>
                @endcan
                @can('approve', $ppmp)
                    <x-button wire:click="approve" variant="success">Final Approve</x-button>
                @endcan
            @endif
            @if($ppmp->status === \App\Enums\PpmpStatus::Approved)
                @can('lock', $ppmp)
                    <x-button wire:click="lock" variant="secondary">Lock</x-button>
                    <x-button wire:click="createRevision('supplemental')" variant="secondary">Create Supplemental</x-button>
                    <x-button wire:click="createRevision('amended')" variant="secondary">Create Amendment</x-button>
                @endcan
            @endif
            <x-button href="{{ route('ppmps.print', $ppmp) }}" variant="secondary"><x-icon name="printer" class="h-4 w-4" /> Print</x-button>
        </x-slot:actions>
    </x-page-header>

    @error('budget')<div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>@enderror

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total ABC" :value="'₱'.number_format($ppmp->total_abc, 2)" icon="banknotes" />
        <x-stat-card label="Items" :value="$items->count()" icon="document-text" accent="indigo" />
        <x-stat-card label="Fiscal Year" :value="$ppmp->fiscalYear?->year" icon="calendar" accent="amber" />
    </div>

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
                                    <p class="text-xs text-slate-400">{{ $item->specification }}</p>
                                </td>
                                <td class="py-2.5 pr-4">{{ rtrim(rtrim($item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ $item->modeOfProcurement?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ $item->fundSource?->name }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium">₱{{ number_format($item->abc, 2) }}</td>
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
