@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $section = 'text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500';
    $remainingFund = $remainingFund ?? null;
    $defaultOpen = $defaultOpen ?? ($sectionIndex === 0);
@endphp

<article
    x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }"
    wire:key="item-{{ $index }}-{{ $item['id'] ?? 'new' }}"
    id="ppmp-item-{{ $index }}"
    class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800
        {{ $focusedItemIndex === $index ? 'ring-2 ring-primary-500/30' : '' }}
        {{ $showMobileNav ? ($focusedItemIndex === $index ? 'block' : 'hidden lg:block') : 'block' }}"
>
    <div class="flex items-start justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50">
        <button
            type="button"
            @click="open = !open"
            class="flex min-w-0 flex-1 items-start gap-2 text-left"
            :aria-expanded="open"
        >
            <svg
                class="mt-0.5 h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
                :class="{ 'rotate-90': open }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-full bg-primary-100 px-2 text-xs font-bold text-primary-800 dark:bg-primary-900/40 dark:text-primary-200">
                        {{ $sectionIndex + 1 }}
                    </span>
                    <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                        {{ $item['item_name'] ?: 'New procurement item' }}
                    </p>
                </div>
                @if($lineAbc > 0)
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        ABC: <span class="font-semibold text-slate-700 dark:text-slate-200">₱{{ number_format($lineAbc, 2) }}</span>
                        @if($remainingFund !== null)
                            &middot; Remaining: <span class="font-semibold {{ $remainingFund < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400' }}">₱{{ number_format($remainingFund, 2) }}</span>
                        @endif
                    </p>
                @endif
            </div>
        </button>
        <div class="flex shrink-0 flex-col items-end gap-2 sm:flex-row sm:items-center">
            <div class="flex flex-col items-end gap-1">
                <label for="expense-class-{{ $index }}" class="sr-only">Expense class</label>
                <select
                    id="expense-class-{{ $index }}"
                    wire:model.live="items.{{ $index }}.expense_class"
                    @click.stop
                    class="rounded-lg border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs font-medium text-slate-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                    title="Move this item to MOOE or Capital Outlay"
                >
                    @foreach($expenseClassOptions as $value => $optionLabel)
                        <option value="{{ $value }}">{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </div>
            @if($canRemove)
                <button
                    type="button"
                    wire:click="removeItem({{ $index }})"
                    wire:confirm="Remove this row from the PPMP?"
                    @click.stop
                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                >
                    Remove
                </button>
            @endif
        </div>
    </div>

    <div x-show="open" x-transition class="space-y-5 p-4 sm:p-5">
        <div>
            <p class="{{ $section }} mb-3">Item details</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Expense class</label>
                    <select wire:model.live="items.{{ $index }}.expense_class" class="{{ $field }}">
                        @foreach($expenseClassOptions as $value => $optionLabel)
                            <option value="{{ $value }}">{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Move this row between MOOE and Capital Outlay.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Type of project to be procured <span class="text-red-500">*</span></label>
                    <select wire:model="items.{{ $index }}.project_type" class="{{ $field }}">
                        @foreach($projectTypeOptions as $value => $optionLabel)
                            <option value="{{ $value }}">{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                    @error("items.$index.project_type") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Item name <span class="text-red-500">*</span></label>
                    <input wire:model.blur="items.{{ $index }}.item_name" type="text" class="{{ $field }}" placeholder="What are you procuring?">
                    @error("items.$index.item_name") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Description</label>
                    <textarea wire:model.blur="items.{{ $index }}.description" rows="2" class="{{ $field }}" placeholder="Brief purpose or scope"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Specification / size</label>
                    <input wire:model.blur="items.{{ $index }}.specification" type="text" class="{{ $field }}" placeholder="Technical specs, dimensions, etc.">
                </div>
                <div>
                    <label class="{{ $label }}">Unit <span class="text-red-500">*</span></label>
                    <input wire:model.blur="items.{{ $index }}.unit" type="text" class="{{ $field }}" placeholder="e.g. unit, lot, set">
                    @error("items.$index.unit") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Quantity <span class="text-red-500">*</span></label>
                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" inputmode="decimal" class="{{ $field }}" placeholder="0">
                    @error("items.$index.quantity") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5 dark:border-slate-800">
            <p class="{{ $section }} mb-3">Cost & schedule</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="{{ $label }}">Est. unit cost <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">₱</span>
                        <input wire:model.live="items.{{ $index }}.estimated_unit_cost" type="number" step="0.01" inputmode="decimal" class="{{ $field }} pl-8" placeholder="0.00">
                    </div>
                    @error("items.$index.estimated_unit_cost") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end sm:col-span-1 lg:col-span-2">
                    <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800/60">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Line ABC</p>
                            <p class="text-lg font-bold text-slate-800 dark:text-slate-100">₱{{ number_format($lineAbc, 2) }}</p>
                        </div>
                        @if($remainingFund !== null)
                            <div class="rounded-lg px-4 py-3 {{ $remainingFund < 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-emerald-50 dark:bg-emerald-900/20' }}">
                                <p class="text-xs font-medium uppercase tracking-wide {{ $remainingFund < 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">Remaining fund</p>
                                <p class="text-lg font-bold {{ $remainingFund < 0 ? 'text-red-700 dark:text-red-300' : 'text-emerald-800 dark:text-emerald-200' }}">₱{{ number_format($remainingFund, 2) }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">of ₱{{ number_format($projectProposalTotalCost, 2) }} project cost</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="{{ $label }}">Schedule start</label>
                    <input wire:model="items.{{ $index }}.schedule_start" type="date" class="{{ $field }}">
                    @error("items.$index.schedule_start") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Schedule end</label>
                    <input wire:model="items.{{ $index }}.schedule_end" type="date" class="{{ $field }}">
                    @error("items.$index.schedule_end") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5 dark:border-slate-800">
            <p class="{{ $section }} mb-3">Procurement & budget</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}">Mode of procurement</label>
                    <select wire:model="items.{{ $index }}.mode_of_procurement_id" class="{{ $field }}">
                        <option value="">Select&hellip;</option>
                        @foreach($modes as $mode)
                            <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" title="Pre-Procurement Conference — NGPA PPMP Column 5">
                        <span class="lg:hidden">Pre-proc conference</span>
                        <span class="hidden lg:inline">Pre-Procurement Conference (Col. 5)</span>
                    </label>
                    <select wire:model="items.{{ $index }}.pre_procurement_conference" class="{{ $field }}">
                        @foreach($preProcurementOptions as $value => $optionLabel)
                            <option value="{{ $value }}">{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                    @error("items.$index.pre_procurement_conference") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Fund source / PAP / UACS</label>
                    <select wire:model="items.{{ $index }}.budget_allocation_id" class="{{ $field }}">
                        <option value="">Select budget allocation&hellip;</option>
                        @foreach($allocations as $alloc)
                            <option value="{{ $alloc->id }}">
                                {{ $alloc->fundSource?->name }} &middot; {{ $alloc->pap?->code }} &middot; ₱{{ number_format($alloc->remaining_balance, 2) }} left
                            </option>
                        @endforeach
                    </select>
                    @if($allocations->isEmpty() && ($fiscal_year_id && $division_id))
                        <p class="mt-1.5 text-xs text-amber-600 dark:text-amber-400">No budget allocations found for this division and fiscal year.</p>
                    @elseif(!$division_id || !$fiscal_year_id)
                        <p class="mt-1.5 text-xs text-slate-500">Select fiscal year and division first to load allocations.</p>
                    @endif
                    @error("items.$index.budget_allocation_id") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Remarks</label>
                    <textarea wire:model.blur="items.{{ $index }}.remarks" rows="2" class="{{ $field }}" placeholder="Optional notes for this line item"></textarea>
                </div>
            </div>
        </div>
    </div>

    @if($showMobileNav && ! $isLastInAll)
        <div class="border-t border-slate-100 px-4 py-3 lg:hidden dark:border-slate-800">
            <button
                type="button"
                wire:click="focusItem({{ $index + 1 }})"
                class="w-full rounded-lg bg-slate-100 py-2.5 text-sm font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200"
            >
                Continue to next row &darr;
            </button>
        </div>
    @endif
</article>
