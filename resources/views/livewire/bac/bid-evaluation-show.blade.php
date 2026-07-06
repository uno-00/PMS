<div>
    <x-page-header title="Bid Evaluation Matrix" :subtitle="$procurement->title">
        <x-slot:actions>
            <x-button href="{{ route('procurements.show', $procurement) }}" variant="secondary" size="sm">Back to Case</x-button>
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    @if($bids->isEmpty())
        <x-empty-state icon="document-text" title="No bids to evaluate" />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Bidder</th>
                            <th class="py-2 pr-4">Compliance</th>
                            <th class="py-2 pr-4 text-center">Score</th>
                            <th class="py-2 pr-4 text-center">Rank</th>
                            <th class="py-2 pr-4">Recommendation</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($bids as $bid)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">
                                    {{ $bid->bidder?->company_name }}
                                    <p class="text-xs font-normal text-slate-400">{{ $bid->bid_no }} &middot; v{{ $bid->version }}</p>
                                </td>
                                <td class="py-3 pr-4 text-xs">
                                    @if($bid->evaluation)
                                        @foreach((array) $bid->evaluation->compliance as $k => $v)
                                            <span class="mr-1 inline-flex items-center rounded-full px-2 py-0.5 {{ $v ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">{{ ucfirst($k) }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-slate-400">Not evaluated</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-center font-medium">{{ $bid->evaluation?->score ?? '—' }}</td>
                                <td class="py-3 pr-4 text-center font-medium">{{ $bid->evaluation?->rank ?? '—' }}</td>
                                <td class="py-3 pr-4 text-xs">{{ $bid->evaluation ? ucfirst(str_replace('_',' ', $bid->evaluation->recommendation)) : '—' }}</td>
                                <td class="py-3 pr-4 text-right">
                                    @can('bid-evaluation.evaluate')
                                        <x-button size="sm" variant="secondary" wire:click="edit('{{ $bid->id }}')">Evaluate</x-button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    @if($editingBidId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="cancel">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Evaluate Bid</h3>
                <div class="mt-4 space-y-3">
                    <p class="text-xs font-semibold uppercase text-slate-400">Compliance Checklist</p>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="compliance.eligibility"> Eligibility</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="compliance.technical"> Technical</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="compliance.financial"> Financial</label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Score (0–100)</label>
                            <input wire:model="score" type="number" step="0.01" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('score') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Rank</label>
                            <input wire:model="rank" type="number" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Recommendation</label>
                        <select wire:model="recommendation" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="recommended">Recommended for Award</option>
                            <option value="not_recommended">Not Recommended</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Remarks</label>
                        <textarea wire:model="remarks" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <x-button variant="secondary" wire:click="cancel">Cancel</x-button>
                    <x-button wire:click="save">Save Evaluation</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
