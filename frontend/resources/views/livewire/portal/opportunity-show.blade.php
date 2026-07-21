<div>
    <x-page-header :title="$procurement->title" :subtitle="'Case No. '.$procurement->case_no.' · '.$procurement->modeOfProcurement?->name">
        <x-slot:actions>
            <x-status-badge :status="$procurement->philgepsPosting?->status" />
            <x-button href="{{ route('bidder.opportunities.index') }}" variant="secondary" size="sm">Back</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Approved Budget for Contract" :value="'₱'.number_format($procurement->abc, 2)" icon="banknotes" />
        <x-stat-card label="Posting Date" :value="$procurement->philgepsPosting?->posting_date?->format('M d, Y') ?? '—'" icon="calendar" />
        <x-stat-card label="Closing Date" :value="$procurement->philgepsPosting?->closing_date?->format('M d, Y') ?? '—'" icon="calendar" accent="amber" />
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="1. Purchase of Bid Documents">
                @if($order)
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-slate-400">Order No.</dt><dd class="font-medium">{{ $order->order_no }}</dd></div>
                        <div><dt class="text-slate-400">Amount</dt><dd class="font-medium">₱{{ number_format($order->amount, 2) }}</dd></div>
                        <div><dt class="text-slate-400">Payment Status</dt><dd><x-status-badge :status="new \App\Support\SimpleStatus($order->payment_status)" /></dd></div>
                        <div><dt class="text-slate-400">OR No.</dt><dd class="font-medium">{{ $order->or_no ?? '—' }}</dd></div>
                    </dl>
                    @if($order->payment_status === 'pending')
                        <p class="mt-3 text-xs text-slate-400">Complete your payment from the <a href="{{ route('bidder.orders.index') }}" class="font-medium text-indigo-600 hover:underline">Bid Doc Orders</a> page.</p>
                    @elseif($canDownloadDocs)
                        <div class="mt-3">
                            @forelse($biddingDocuments as $doc)
                                <a href="{{ route('documents.download', $doc) }}" class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-indigo-600 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                                    <x-icon name="download" class="h-4 w-4" /> {{ $doc->original_filename }}
                                </a>
                            @empty
                                <p class="text-xs text-slate-400">The BAC Secretariat has not uploaded downloadable bidding documents yet. Please coordinate directly if needed.</p>
                            @endforelse
                        </div>
                    @endif
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">Fee for this ABC bracket: <strong>₱{{ number_format($this->bidDocumentFee(), 2) }}</strong></p>
                    <x-button wire:click="orderBidDocuments" class="mt-3" size="sm">Generate Order</x-button>
                @endif
            </x-card>

            <x-card title="2. Submit Bid">
                @if(!$order || $order->payment_status !== 'paid')
                    <x-empty-state icon="document-text" title="Purchase bid documents first" description="You must pay for the bidding documents before you can submit a bid." />
                @elseif(!$isOpen)
                    <x-empty-state icon="x-mark" title="Submission closed" description="Late submission is disabled once the closing date has passed." />
                @else
                    <form wire:submit="submitBid" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Technical Proposal</label>
                            <input type="file" wire:model="technicalFile" class="mt-1 block w-full text-xs">
                            @error('technicalFile') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Financial Proposal</label>
                            <input type="file" wire:model="financialFile" class="mt-1 block w-full text-xs">
                            @error('financialFile') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Eligibility Documents</label>
                            <input type="file" wire:model="eligibilityFile" class="mt-1 block w-full text-xs">
                            @error('eligibilityFile') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <x-button type="submit" wire:loading.attr="disabled" wire:target="submitBid">Submit Bid (Encrypted Upload)</x-button>
                    </form>
                @endif

                @if($myBids->isNotEmpty())
                    <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <p class="mb-2 text-xs font-semibold uppercase text-slate-400">Your Submission History</p>
                        @foreach($myBids as $bid)
                            <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0 dark:border-slate-800">
                                <span>{{ $bid->bid_no }} &middot; v{{ $bid->version }}</span>
                                <span class="text-xs text-slate-400">{{ $bid->submitted_at?->format('M d, Y g:ia') }} &middot; {{ ucfirst($bid->status) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        <x-card title="Ask Clarification">
            <form wire:submit="askClarification" class="space-y-2">
                <textarea wire:model="question" rows="3" placeholder="Type your question about this procurement…" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                @error('question') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <x-button type="submit" size="sm" wire:loading.attr="disabled" wire:target="askClarification">Send</x-button>
            </form>

            <div class="mt-4 space-y-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                @forelse($procurement->clarifications as $c)
                    <div class="rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-800/50">
                        <p class="font-medium text-slate-700 dark:text-slate-200">Q: {{ $c->question }}</p>
                        @if($c->answer)
                            <p class="mt-1 text-slate-500 dark:text-slate-400">A: {{ $c->answer }}</p>
                        @else
                            <p class="mt-1 text-xs text-amber-600">Awaiting response…</p>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400">No clarifications asked yet.</p>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
