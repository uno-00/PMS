<div>
    <x-page-header :title="'PhilGEPS Posting '.($posting->reference_no ?: '· '.$posting->id)" :subtitle="$posting->procurement?->title">
        <x-slot:actions>
            <x-status-badge :status="$posting->status" class="!text-sm" />
            @if($posting->procurement)
                <x-button href="{{ route('procurements.show', $posting->procurement) }}" variant="secondary">View Procurement</x-button>
            @endif
            @can('update', $posting)
                <x-button href="{{ route('philgeps.edit', $posting) }}" variant="secondary">Edit</x-button>
            @endcan
            <x-button href="{{ route('philgeps.index') }}" variant="secondary">Back to Postings</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Posting Date" :value="$posting->posting_date?->format('M d, Y') ?? '—'" icon="calendar" />
        <x-stat-card label="Closing Date" :value="$posting->closing_date?->format('M d, Y') ?? '—'" icon="calendar" accent="amber" />
        <x-stat-card label="Type" :value="$posting->is_manual ? 'Manual' : 'API'" icon="globe" accent="indigo" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Posting">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">Reference No.</dt><dd class="mt-0.5 font-mono text-slate-700 dark:text-slate-200">{{ $posting->reference_no ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Status</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $posting->status->label() }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Posted by</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $posting->postedBy?->name ?? '—' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Procurement">
            @if($posting->procurement)
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs uppercase text-slate-400">Case No.</dt><dd class="mt-0.5 font-mono text-slate-700 dark:text-slate-200">{{ $posting->procurement->case_no ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">Title</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $posting->procurement->title }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-400">Mode of Procurement</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $posting->procurement->modeOfProcurement?->name ?? '—' }}</dd></div>
                </dl>
            @else
                <p class="text-sm text-slate-500">No linked procurement case.</p>
            @endif
            @if($posting->remarks)
                <div class="mt-4 border-t border-slate-100 pt-3 dark:border-slate-800">
                    <dt class="text-xs uppercase text-slate-400">Remarks</dt>
                    <dd class="mt-0.5 whitespace-pre-wrap text-slate-700 dark:text-slate-200">{{ $posting->remarks }}</dd>
                </div>
            @endif
        </x-card>
    </div>
</div>
