<div>
    <x-page-header :title="$marketScoping->project_name" :subtitle="'Control No. '.$marketScoping->control_no.' &middot; '.($marketScoping->end_user_unit ?: $marketScoping->division?->name)">
        <x-slot:actions>
            <x-status-badge :status="$marketScoping->status" class="!text-sm" />
            @can('update', $marketScoping)
                <x-button href="{{ route('market-scoping.edit', $marketScoping) }}" variant="secondary">Edit</x-button>
            @endcan
            @can('approve', $marketScoping)
                <x-button wire:click="approve" variant="success">Approve</x-button>
            @endcan
            <x-button href="{{ route('market-scoping.print', $marketScoping) }}" variant="secondary"><x-icon name="printer" class="h-4 w-4" /> Print</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Estimated Budget" :value="'₱'.number_format($marketScoping->estimated_budget, 2)" icon="banknotes" />
        <x-stat-card label="Fiscal Year" :value="$marketScoping->fiscalYear?->year ?? '—'" icon="calendar" accent="amber" />
        <x-stat-card label="Period of Market Scoping" :value="\App\Support\MarketScopingPrintFormatter::periodRange($marketScoping)" icon="clock" accent="indigo" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Agency Information">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">Procuring Entity</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $marketScoping->procuring_entity ?: '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">End-User / Implementing Unit</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $marketScoping->end_user_unit ?: $marketScoping->division?->name }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Representative</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $marketScoping->representative_name ?: '—' }} @if($marketScoping->representative_designation)<span class="text-slate-400">({{ $marketScoping->representative_designation }})</span>@endif</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Expected Delivery</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ \App\Support\MarketScopingPrintFormatter::monthYear($marketScoping->expected_delivery) }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Signatories">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">Prepared by</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $marketScoping->preparedBy?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Approved by</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $marketScoping->approvedBy?->name ?? '—' }} @if($marketScoping->approved_at)<span class="text-slate-400">({{ $marketScoping->approved_at->format('M d, Y') }})</span>@endif</dd></div>
            </dl>
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Activities Conducted">
            <ul class="space-y-3 text-sm">
                @foreach(\App\Support\MarketScopingPrintFormatter::activityDefinitions() as $activity)
                    @if(\App\Support\MarketScopingPrintFormatter::activityChecked($marketScoping, $activity['key']))
                        <li class="rounded-lg bg-slate-50 p-3 dark:bg-slate-900/50">
                            <p class="font-medium text-slate-700 dark:text-slate-200">{{ $activity['label'] }}</p>
                            <p class="mt-1 whitespace-pre-wrap text-xs text-slate-500">{{ \App\Support\MarketScopingPrintFormatter::activityDocumentation($marketScoping, $activity['key']) }}</p>
                        </li>
                    @endif
                @endforeach
            </ul>
        </x-card>

        <x-card title="Market Scoping Results">
            <div class="space-y-3 text-sm">
                @foreach(\App\Support\MarketScopingPrintFormatter::parameterDefinitions() as $parameter)
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-900/50">
                        <p class="font-medium text-slate-700 dark:text-slate-200">{{ $parameter['letter'] }}. {{ $parameter['label'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">Considered: {{ \App\Support\MarketScopingPrintFormatter::parameterConsidered($marketScoping, $parameter['key']) }}</p>
                        <p class="mt-1 whitespace-pre-wrap text-xs text-slate-600 dark:text-slate-300">{{ \App\Support\MarketScopingPrintFormatter::parameterRecommendations($marketScoping, $parameter['key']) }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
