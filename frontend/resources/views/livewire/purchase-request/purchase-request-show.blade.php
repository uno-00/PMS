<div>
    <x-page-header :title="$purchaseRequest->pr_no" :subtitle="$purchaseRequest->purpose">
        <x-slot:actions>
            <x-status-badge :status="$purchaseRequest->status" class="!text-sm" />
            @if($purchaseRequest->status->value === 'division_chief')
                @can('divisionChiefReview', \App\Models\Procurement\PurchaseRequest::class)
                    <x-button wire:click="divisionChiefApprove" variant="success">Approve (Division Chief)</x-button>
                    <x-button wire:click="$set('showRejectModal', true)" variant="danger">Reject</x-button>
                @endcan
            @endif
            @if($purchaseRequest->status->value === 'planning')
                @can('planningReview', \App\Models\Procurement\PurchaseRequest::class)
                    <x-button wire:click="planningApprove" variant="success">Approve (Planning)</x-button>
                    <x-button wire:click="$set('showRejectModal', true)" variant="danger">Reject</x-button>
                @endcan
            @endif
            @if($purchaseRequest->status->value === 'budget')
                @can('budgetReview', \App\Models\Procurement\PurchaseRequest::class)
                    <x-button wire:click="budgetApprove" variant="success">Approve (Budget)</x-button>
                    <x-button wire:click="$set('showRejectModal', true)" variant="danger">Reject</x-button>
                @endcan
            @endif
            @if($purchaseRequest->status->value === 'hope')
                @can('hopeApprove', \App\Models\Procurement\PurchaseRequest::class)
                    <x-button wire:click="hopeApprove" variant="success">Approve (HOPE)</x-button>
                    <x-button wire:click="$set('showRejectModal', true)" variant="danger">Reject</x-button>
                @endcan
            @endif
            @if($purchaseRequest->status->value === 'approved')
                @can('bac-calendar.manage')
                    @if(!$purchaseRequest->certificateOfAvailabilityOfFunds || $purchaseRequest->certificateOfAvailabilityOfFunds->status->value !== 'generated')
                        <x-button wire:click="initiateProcurement" variant="secondary">Open Procurement Case</x-button>
                    @endif
                @endcan
            @endif
            <x-button href="{{ route('purchase-requests.print', $purchaseRequest) }}" variant="secondary"><x-icon name="printer" class="h-4 w-4" /> Print</x-button>
        </x-slot:actions>
    </x-page-header>

    @error('budget')<div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>@enderror

    @if($purchaseRequest->certificateOfAvailabilityOfFunds)
        <div class="mb-4 flex items-center justify-between rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
            <span>Certificate of Availability of Funds {{ $purchaseRequest->certificateOfAvailabilityOfFunds->caf_no }} has been generated.</span>
            <a href="{{ route('cafs.show', $purchaseRequest->certificateOfAvailabilityOfFunds) }}" class="font-semibold hover:underline">View CAF &rarr;</a>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Amount" :value="'₱'.number_format($purchaseRequest->total_amount, 2)" icon="banknotes" />
        <x-stat-card label="Division" :value="$purchaseRequest->division?->name" icon="building-office" accent="indigo" />
        <x-stat-card label="PPMP Reference" :value="$purchaseRequest->ppmp?->control_no" icon="document-text" accent="amber" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Requested Items" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Item</th>
                            <th class="py-2 pr-4 text-right">Qty</th>
                            <th class="py-2 pr-4 text-right">Unit Cost</th>
                            <th class="py-2 pr-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($items as $item)
                            <tr>
                                <td class="py-2.5 pr-4">{{ $item->item_name }}</td>
                                <td class="py-2.5 pr-4 text-right">{{ rtrim(rtrim($item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                                <td class="py-2.5 pr-4 text-right">₱{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium">₱{{ number_format($item->amount, 2) }}</td>
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

    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reject Purchase Request</h3>
                <textarea wire:model="remarks" rows="3" placeholder="Reason for rejection" class="mt-3 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-4 flex justify-end gap-2">
                    <x-button wire:click="$set('showRejectModal', false)" variant="secondary">Cancel</x-button>
                    <x-button wire:click="reject" variant="danger">Reject</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
