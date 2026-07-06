<div>
    <x-page-header title="Bidders / Suppliers" subtitle="Registered suppliers, their eligibility status, and PhilGEPS registration details." />

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach(['pending' => 'Pending', 'verified' => 'Verified', 'suspended' => 'Suspended', 'rejected' => 'Rejected'] as $key => $label)
            <div class="rounded-lg border border-slate-200 bg-white p-3 text-center dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $statusCounts[$key] ?? 0 }}</p>
                <p class="text-xs text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Search company name..." class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="suspended">Suspended</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    @if($bidders->isEmpty())
        <x-empty-state icon="building-office" title="No bidders registered yet" />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Company</th>
                            <th class="py-2 pr-4">Contact Person</th>
                            <th class="py-2 pr-4">Email</th>
                            <th class="py-2 pr-4">PhilGEPS No.</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($bidders as $bidder)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $bidder->company_name }}</td>
                                <td class="py-3 pr-4">{{ $bidder->contact_person }}</td>
                                <td class="py-3 pr-4">{{ $bidder->email }}</td>
                                <td class="py-3 pr-4 font-mono text-xs">{{ $bidder->philgeps_registration_no ?? '—' }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="new \App\Support\SimpleStatus($bidder->status)" /></td>
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('bidders.show', $bidder) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $bidders->links() }}</div>
        </x-card>
    @endif
</div>
