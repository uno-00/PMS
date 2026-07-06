<div>
    <x-page-header :title="$bidder->company_name" :subtitle="$bidder->contact_person.' · '.$bidder->email">
        <x-slot:actions>
            <x-status-badge :status="new \App\Support\SimpleStatus($bidder->status)" class="!text-sm" />
            @can('verify', $bidder)
                @if($bidder->status !== 'verified')
                    <x-button size="sm" variant="success" wire:click="verify">Verify</x-button>
                @endif
                @if($bidder->status !== 'suspended')
                    <x-button size="sm" variant="danger" wire:click="openSuspendModal">Suspend</x-button>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    @if($bidder->status === 'suspended' && $bidder->remarks)
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">Suspended: {{ $bidder->remarks }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Business Information">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs uppercase text-slate-400">Business Type</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ ucfirst(str_replace('_', ' ', $bidder->business_type ?? '—')) }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">TIN</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ $bidder->tin ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">PhilGEPS Registration No.</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ $bidder->philgeps_registration_no ?? '—' }} @if($bidder->philgeps_registration_expiry) <span class="text-xs text-slate-400">(exp. {{ $bidder->philgeps_registration_expiry->format('M d, Y') }})</span>@endif</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">Mayor's Permit No.</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ $bidder->mayor_permit_no ?? '—' }} @if($bidder->mayor_permit_expiry) <span class="text-xs text-slate-400">(exp. {{ $bidder->mayor_permit_expiry->format('M d, Y') }})</span>@endif</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">Tax Clearance No.</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ $bidder->tax_clearance_no ?? '—' }} @if($bidder->tax_clearance_expiry) <span class="text-xs text-slate-400">(exp. {{ $bidder->tax_clearance_expiry->format('M d, Y') }})</span>@endif</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">SEC / DTI Registration No.</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ $bidder->sec_dti_registration_no ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">PCAB License No.</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ $bidder->pcab_license_no ?? '—' }} @if($bidder->pcab_license_expiry) <span class="text-xs text-slate-400">(exp. {{ $bidder->pcab_license_expiry->format('M d, Y') }})</span>@endif</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-400">Address</dt><dd class="text-sm text-slate-700 dark:text-slate-200">{{ $bidder->address ?? '—' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Eligibility Documents">
                <div class="space-y-2">
                    @foreach(\App\Models\Supplier\Bidder::DOCUMENT_CATEGORIES as $category => $label)
                        @php $doc = $bidder->documents->firstWhere('category', $category); @endphp
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3 dark:border-slate-800">
                            <div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</p>
                                <p class="text-xs text-slate-400">{{ $doc ? 'v'.$doc->version.' · '.$doc->created_at->format('M d, Y') : 'Not uploaded' }}</p>
                            </div>
                            @if($doc)
                                <x-button size="sm" variant="secondary" wire:click="downloadDocument('{{ $doc->id }}')">Download</x-button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card title="Bid Submissions">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead><tr class="text-left text-xs uppercase text-slate-400"><th class="py-2 pr-4">Bid No.</th><th class="py-2 pr-4">Case</th><th class="py-2 pr-4">Submitted</th><th class="py-2 pr-4">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($bidder->bidSubmissions as $bid)
                            <tr>
                                <td class="py-2.5 pr-4 font-mono text-xs">{{ $bid->bid_no }}</td>
                                <td class="py-2.5 pr-4">{{ $bid->procurement?->case_no }}</td>
                                <td class="py-2.5 pr-4">{{ $bid->submitted_at?->format('M d, Y g:ia') }} @if($bid->is_late) <span class="text-red-600">(late)</span>@endif</td>
                                <td class="py-2.5 pr-4"><x-status-badge :status="new \App\Support\SimpleStatus($bid->status)" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state icon="document-text" title="No bid submissions yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            <x-card title="Notice of Awards">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead><tr class="text-left text-xs uppercase text-slate-400"><th class="py-2 pr-4">NOA No.</th><th class="py-2 pr-4">Case</th><th class="py-2 pr-4 text-right">Amount</th><th class="py-2 pr-4">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($bidder->noticeOfAwards as $noa)
                            <tr>
                                <td class="py-2.5 pr-4 font-mono text-xs">{{ $noa->noa_no }}</td>
                                <td class="py-2.5 pr-4">{{ $noa->procurement?->case_no }}</td>
                                <td class="py-2.5 pr-4 text-right">₱{{ number_format($noa->amount, 2) }}</td>
                                <td class="py-2.5 pr-4"><x-status-badge :status="$noa->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state icon="check-badge" title="No awards yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Bid Document Orders">
                <div class="space-y-2">
                    @forelse($bidder->bidDocumentOrders as $order)
                        <div class="rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-800">
                            <p class="font-mono text-xs text-slate-400">{{ $order->order_no }}</p>
                            <p class="text-slate-700 dark:text-slate-200">{{ $order->procurement?->case_no }}</p>
                            <div class="mt-1 flex justify-between text-xs">
                                <span>₱{{ number_format($order->amount, 2) }}</span>
                                <x-status-badge :status="new \App\Support\SimpleStatus($order->payment_status)" />
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No bid document orders yet.</p>
                    @endforelse
                </div>
            </x-card>

            <x-card title="Clarifications">
                <div class="space-y-2">
                    @forelse($bidder->clarifications as $c)
                        <div class="rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-800">
                            <p class="text-slate-700 dark:text-slate-200">{{ \Illuminate\Support\Str::limit($c->question, 80) }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $c->procurement?->case_no }} · {{ $c->answered_at ? 'Answered' : 'Pending' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No clarifications asked yet.</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    @if($showSuspendModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="$set('showSuspendModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Suspend Bidder</h3>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reason for suspension</label>
                    <textarea wire:model="suspend_remarks" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    @error('suspend_remarks') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <x-button variant="secondary" wire:click="$set('showSuspendModal', false)">Cancel</x-button>
                    <x-button variant="danger" wire:click="suspend">Suspend</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
