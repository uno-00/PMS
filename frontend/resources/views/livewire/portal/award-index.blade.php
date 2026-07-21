<div>
    <x-page-header title="My Awards" subtitle="Track Notices of Award, Notice to Proceed, and Purchase Orders." />

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    @if($awards->isEmpty())
        <x-empty-state icon="check-badge" title="No awards yet" description="Awarded procurement cases will appear here." />
    @else
        <div class="space-y-4">
            @foreach($awards as $noa)
                <x-card>
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $noa->procurement->title }}</p>
                            <p class="mt-1 text-xs text-slate-400">NOA No. {{ $noa->noa_no }} &middot; Awarded {{ $noa->issued_at?->format('M d, Y') }}</p>
                            <p class="mt-1 text-sm font-medium text-emerald-600">₱{{ number_format($noa->amount, 2) }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <x-status-badge :status="$noa->status" />
                            @if($noa->status === \App\Enums\NoticeOfAwardStatus::Awarded)
                                <div class="flex gap-2">
                                    <x-button wire:click="respond('{{ $noa->id }}', true)" variant="success" size="sm">Accept</x-button>
                                    <x-button wire:click="respond('{{ $noa->id }}', false)" variant="danger" size="sm">Decline</x-button>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($noa->procurement->noticeToProceed || $noa->procurement->purchaseOrders->isNotEmpty())
                        <div class="mt-4 grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 dark:border-slate-800 sm:grid-cols-2">
                            @if($ntp = $noa->procurement->noticeToProceed)
                                <div class="rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-800/50">
                                    <p class="font-medium text-slate-700 dark:text-slate-200">Notice to Proceed</p>
                                    <p class="text-xs text-slate-400">{{ $ntp->ntp_no }} &middot; Effective {{ $ntp->effectivity_date->format('M d, Y') }}</p>
                                </div>
                            @endif
                            @foreach($noa->procurement->purchaseOrders as $po)
                                <div class="rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-800/50">
                                    <p class="font-medium text-slate-700 dark:text-slate-200">Purchase Order {{ $po->po_no }}</p>
                                    <p class="text-xs text-slate-400">₱{{ number_format($po->total_amount, 2) }} &middot; <x-status-badge :status="$po->status" /></p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            @endforeach
        </div>
        <div class="mt-4">{{ $awards->links() }}</div>
    @endif
</div>
