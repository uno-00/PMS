<div>
    <x-page-header title="Supplier Dashboard" :subtitle="'Welcome back, '.$bidder->company_name.'.'">
        <x-slot:actions>
            @if($bidder->status === 'pending')
                <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                    <x-icon name="check-badge" class="mr-1 h-4 w-4" /> Verification Pending
                </span>
            @elseif($bidder->status === 'verified')
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                    <x-icon name="check" class="mr-1 h-4 w-4" /> Verified Supplier
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Suspended</span>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if($bidder->status === 'pending')
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-900/20 dark:text-amber-300">
            Your account is awaiting verification by the agency's BAC Secretariat. Please make sure your
            <a href="{{ route('bidder.profile') }}" class="font-semibold underline">eligibility documents</a> are complete and up to date.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Open Opportunities" :value="$openOpportunities->count()" icon="globe" />
        <x-stat-card label="Active Bids" :value="$activeBids->count()" icon="document-text" accent="indigo" />
        <x-stat-card label="Pending Doc Orders" :value="$pendingOrders" icon="credit-card" accent="amber" />
        <x-stat-card label="Total Awards" :value="$totalAwards" icon="check-badge" accent="emerald" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Open Opportunities">
            @forelse($openOpportunities as $posting)
                <div class="flex items-center justify-between border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $posting->procurement->title }}</p>
                        <p class="text-xs text-slate-400">Closes {{ $posting->closing_date->format('M d, Y') }} &middot; ABC ₱{{ number_format($posting->procurement->abc, 2) }}</p>
                    </div>
                    <x-button href="{{ route('bidder.opportunities.show', $posting->procurement) }}" variant="secondary" size="sm">View</x-button>
                </div>
            @empty
                <x-empty-state icon="globe" title="No open opportunities right now" description="Check back later for new PhilGEPS postings." />
            @endforelse
            <div class="mt-3 text-right">
                <a href="{{ route('bidder.opportunities.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">View all opportunities &rarr;</a>
            </div>
        </x-card>

        <x-card title="Recent Bid Submissions">
            @forelse($activeBids as $bid)
                <div class="flex items-center justify-between border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $bid->procurement->title }}</p>
                        <p class="text-xs text-slate-400">Bid No. {{ $bid->bid_no }} &middot; v{{ $bid->version }} &middot; {{ ucfirst($bid->status) }}</p>
                    </div>
                    <x-button href="{{ route('bidder.opportunities.show', $bid->procurement) }}" variant="secondary" size="sm">View</x-button>
                </div>
            @empty
                <x-empty-state icon="document-text" title="No bids submitted yet" />
            @endforelse
        </x-card>
    </div>
</div>
