<div>
    <x-page-header title="Open Opportunities" subtitle="Government procurement opportunities posted to PhilGEPS." />

    <div class="mb-4 flex flex-wrap gap-2">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Search by title or case no." class="w-64 rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="open">Open for Submission</option>
            <option value="closed">Closed</option>
            <option value="">All</option>
        </select>
    </div>

    @if($postings->isEmpty())
        <x-empty-state icon="globe" title="No opportunities found" />
    @else
        <div class="space-y-3">
            @foreach($postings as $posting)
                <x-card>
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $posting->procurement->title }}</p>
                            <p class="mt-1 text-xs text-slate-400">
                                Case No. {{ $posting->procurement->case_no }} &middot; {{ $posting->procurement->modeOfProcurement?->name }}
                                &middot; ABC ₱{{ number_format($posting->procurement->abc, 2) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Posted {{ $posting->posting_date->format('M d, Y') }} &middot; Closes {{ $posting->closing_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <x-status-badge :status="$posting->status" />
                            <x-button href="{{ route('bidder.opportunities.show', $posting->procurement) }}" size="sm">View Details</x-button>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
        <div class="mt-4">{{ $postings->links() }}</div>
    @endif
</div>
