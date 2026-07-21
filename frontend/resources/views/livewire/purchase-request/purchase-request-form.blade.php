<div>
    <x-page-header :title="$purchaseRequest ? 'Edit Purchase Request' : 'New Purchase Request'" subtitle="Purchase Requests may only be created against an Approved or Locked PPMP. Balances are checked in real time." />

    @error('lines')<div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>@enderror

    <x-card title="Request Details">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Fiscal Year</label>
                <select wire:model.live="fiscal_year_id" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">Select&hellip;</option>
                    @foreach($fiscalYears as $fy)<option value="{{ $fy->id }}">{{ $fy->year }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Division</label>
                <select wire:model.live="division_id" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">Select&hellip;</option>
                    @foreach($divisions as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Approved PPMP</label>
                <select wire:model.live="ppmp_id" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">Select&hellip;</option>
                    @foreach($ppmps as $p)<option value="{{ $p->id }}">{{ $p->title }} ({{ $p->control_no }})</option>@endforeach
                </select>
                @error('ppmp_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-3">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Purpose</label>
                <textarea wire:model="purpose" rows="2" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                @error('purpose') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </x-card>

    @if($ppmp_id)
        <x-card title="Available PPMP Items" class="mt-6">
            @if($availableItems->isEmpty())
                <p class="text-sm text-slate-400">No items with remaining balance in this PPMP.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-xs uppercase text-slate-400">
                                <th class="py-2 pr-4">Item</th>
                                <th class="py-2 pr-4 text-right">Available Balance</th>
                                <th class="py-2 pr-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($availableItems as $item)
                                <tr>
                                    <td class="py-2.5 pr-4">{{ $item->item_name }}</td>
                                    <td class="py-2.5 pr-4 text-right">₱{{ number_format($item->available_balance, 2) }}</td>
                                    <td class="py-2.5 pr-4 text-right">
                                        <button type="button" wire:click="addLine('{{ $item->id }}')" class="text-xs font-medium text-primary-700 hover:underline dark:text-primary-400">+ Add to request</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <x-card title="Requested Items" class="mt-6">
            @if(empty($lines))
                <p class="text-sm text-slate-400">No items added yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($lines as $index => $line)
                        <div wire:key="line-{{ $index }}" class="grid grid-cols-1 gap-3 rounded-lg border border-slate-200 p-3 dark:border-slate-800 sm:grid-cols-5">
                            <div class="sm:col-span-2">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $line['item_name'] }}</p>
                                <p class="text-xs text-slate-400">Available: ₱{{ number_format($line['available'], 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500">Quantity</label>
                                <input wire:model="lines.{{ $index }}.quantity" type="number" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500">Unit Cost</label>
                                <input wire:model="lines.{{ $index }}.unit_cost" type="number" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                            </div>
                            <div class="flex items-end justify-between">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">₱{{ number_format((float)($line['quantity'] ?: 0) * (float)($line['unit_cost'] ?: 0), 2) }}</span>
                                <button type="button" wire:click="removeLine({{ $index }})" class="text-xs font-medium text-red-600 hover:underline">Remove</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <div class="mt-6 flex items-center gap-3">
            <x-button wire:click="save(false)" variant="secondary">Save as Draft</x-button>
            <x-button wire:click="save(true)">Save & Submit for Review</x-button>
            <x-button href="{{ route('purchase-requests.index') }}" variant="secondary">Cancel</x-button>
        </div>
    @endif
</div>
