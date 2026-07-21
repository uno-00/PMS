<div>
    <x-page-header :title="$caf->caf_no" :subtitle="'Purchase Request '.$caf->purchaseRequest?->pr_no">
        <x-slot:actions>
            <x-status-badge :status="$caf->status" class="!text-sm" />
            @can('certify', $caf)
                @if($caf->status === \App\Enums\CafStatus::Generated)
                    <x-button wire:click="certify" variant="success">Certify</x-button>
                @endif
            @endcan
            @can('approve', $caf)
                @if($caf->status === \App\Enums\CafStatus::Certified)
                    <x-button wire:click="approve" variant="success">Approve</x-button>
                @endif
            @endcan
            @can('print', $caf)
                <x-button href="{{ route('cafs.print', $caf) }}" variant="secondary"><x-icon name="printer" class="h-4 w-4" /> Print</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Amount Certified" :value="'₱'.number_format($caf->amount, 2)" icon="banknotes" />
        <x-stat-card label="Remaining Budget" :value="'₱'.number_format($caf->remaining_budget, 2)" icon="chart-pie" accent="emerald" />
        <x-stat-card label="Fund Source" :value="$caf->fundSource?->name" icon="check-badge" accent="indigo" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Details" class="lg:col-span-2">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Purchase Request</dt><dd class="font-medium text-slate-700 dark:text-slate-200">{{ $caf->purchaseRequest?->pr_no }}</dd></div>
                <div><dt class="text-slate-400">Purpose</dt><dd class="font-medium text-slate-700 dark:text-slate-200">{{ $caf->purchaseRequest?->purpose }}</dd></div>
                <div><dt class="text-slate-400">UACS Code</dt><dd class="font-medium text-slate-700 dark:text-slate-200">{{ $caf->uacsCode?->code }}</dd></div>
                <div><dt class="text-slate-400">Verification Code</dt><dd class="font-mono text-xs text-slate-500">{{ $caf->verification_code }}</dd></div>
            </dl>
        </x-card>
        <x-card title="Workflow History">
            <x-workflow-timeline :history="$history" />
        </x-card>
    </div>
</div>
