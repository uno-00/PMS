@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $section = 'text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500';
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header
        :title="$ppmp ? 'Edit PPMP' : 'New PPMP'"
        subtitle="Plan details, procurement items, ABC, schedule, and budget allocation."
    >
        @if($ppmp)
            <x-slot:actions>
                <x-button href="{{ route('ppmps.show', $ppmp) }}" variant="secondary" size="sm" class="hidden sm:inline-flex">Back to PPMP</x-button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <form id="ppmp-form" wire:submit="save" class="space-y-5 sm:space-y-6">
        <x-card title="Plan Details">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="{{ $label }}">Title</label>
                    <input wire:model="title" type="text" placeholder="e.g. FY 2026 Office Supplies PPMP" class="{{ $field }}">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Fiscal Year</label>
                    <select wire:model.live="fiscal_year_id" class="{{ $field }}">
                        <option value="">Select&hellip;</option>
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                        @endforeach
                    </select>
                    @error('fiscal_year_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Division</label>
                    <select wire:model.live="division_id" class="{{ $field }}">
                        <option value="">Select&hellip;</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                    @error('division_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">PPMP Type</label>
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach($documentTypeOptions as $value => $labelText)
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="radio" wire:model="document_type" value="{{ $value }}" class="border-slate-300 text-primary-600 focus:ring-primary-500">
                                {{ $labelText }}
                            </label>
                        @endforeach
                    </div>
                    @error('document_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Procurement Items</h3>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ count($items) }} {{ Str::plural('item', count($items)) }} &middot; Total ABC ₱{{ number_format($this->totalAbc, 2) }}
                    </p>
                </div>
                <x-button type="button" wire:click="addItem" variant="secondary" size="sm" class="w-full sm:w-auto">
                    + Add item
                </x-button>
            </div>

            {{-- Mobile item switcher --}}
            @if(count($items) > 1)
                <div class="mb-4 -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 lg:hidden" role="tablist" aria-label="Procurement items">
                    @foreach($items as $index => $item)
                        @php $lineAbc = \App\Livewire\Planning\PpmpForm::lineAbc($item); @endphp
                        <button
                            type="button"
                            wire:click="focusItem({{ $index }})"
                            role="tab"
                            aria-selected="{{ $focusedItemIndex === $index ? 'true' : 'false' }}"
                            class="shrink-0 rounded-full border px-3 py-2 text-left text-sm transition
                                {{ $focusedItemIndex === $index
                                    ? 'border-primary-600 bg-primary-50 font-semibold text-primary-800 dark:border-primary-500 dark:bg-primary-900/30 dark:text-primary-200'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}"
                        >
                            <span class="block whitespace-nowrap">#{{ $index + 1 }}</span>
                            <span class="block max-w-[8rem] truncate text-xs font-normal opacity-80">
                                {{ $item['item_name'] ?: 'Untitled' }}
                            </span>
                            @if($lineAbc > 0)
                                <span class="block text-xs font-medium">₱{{ number_format($lineAbc, 0) }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="space-y-4 lg:space-y-5">
                @foreach($items as $index => $item)
                    @php $lineAbc = \App\Livewire\Planning\PpmpForm::lineAbc($item); @endphp
                    <article
                        wire:key="item-{{ $index }}-{{ $item['id'] ?? 'new' }}"
                        id="ppmp-item-{{ $index }}"
                        class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800
                            {{ $focusedItemIndex === $index ? 'ring-2 ring-primary-500/30' : '' }}
                            {{ count($items) > 1 ? ($focusedItemIndex === $index ? 'block' : 'hidden lg:block') : 'block' }}"
                    >
                        {{-- Item header --}}
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-full bg-primary-100 px-2 text-xs font-bold text-primary-800 dark:bg-primary-900/40 dark:text-primary-200">
                                        {{ $index + 1 }}
                                    </span>
                                    <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                                        {{ $item['item_name'] ?: 'New procurement item' }}
                                    </p>
                                </div>
                                @if($lineAbc > 0)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        ABC: <span class="font-semibold text-slate-700 dark:text-slate-200">₱{{ number_format($lineAbc, 2) }}</span>
                                    </p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if(count($items) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $index }})"
                                        wire:confirm="Remove this item from the PPMP?"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        Remove
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-5 p-4 sm:p-5">
                            {{-- Section: Item details --}}
                            <div>
                                <p class="{{ $section }} mb-3">Item details</p>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                                        <input wire:model.blur="items.{{ $index }}.quantity" type="number" step="0.01" inputmode="decimal" class="{{ $field }}" placeholder="0">
                                        @error("items.$index.quantity") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Section: Cost & schedule --}}
                            <div class="border-t border-slate-100 pt-5 dark:border-slate-800">
                                <p class="{{ $section }} mb-3">Cost &amp; schedule</p>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <label class="{{ $label }}">Est. unit cost <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">₱</span>
                                            <input wire:model.blur="items.{{ $index }}.estimated_unit_cost" type="number" step="0.01" inputmode="decimal" class="{{ $field }} pl-8" placeholder="0.00">
                                        </div>
                                        @error("items.$index.estimated_unit_cost") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="flex items-end sm:col-span-1 lg:col-span-2">
                                        <div class="w-full rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800/60">
                                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Line ABC</p>
                                            <p class="text-lg font-bold text-slate-800 dark:text-slate-100">₱{{ number_format($lineAbc, 2) }}</p>
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

                            {{-- Section: Procurement & budget --}}
                            <div class="border-t border-slate-100 pt-5 dark:border-slate-800">
                                <p class="{{ $section }} mb-3">Procurement &amp; budget</p>
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
                                        <label class="{{ $label }}">Fund source / PAP / UACS <span class="text-red-500">*</span></label>
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

                        {{-- Mobile: next item navigation --}}
                        @if(count($items) > 1 && $index < count($items) - 1)
                            <div class="border-t border-slate-100 px-4 py-3 lg:hidden dark:border-slate-800">
                                <button
                                    type="button"
                                    wire:click="focusItem({{ $index + 1 }})"
                                    class="w-full rounded-lg bg-slate-100 py-2.5 text-sm font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    Continue to item #{{ $index + 2 }} &darr;
                                </button>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </x-card>

        {{-- Desktop actions --}}
        <div class="hidden items-center justify-between gap-4 lg:flex">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ count($items) }} {{ Str::plural('item', count($items)) }} &middot; Total ABC <span class="font-semibold text-slate-800 dark:text-slate-100">₱{{ number_format($this->totalAbc, 2) }}</span>
            </p>
            <div class="flex items-center gap-3">
                <x-button href="{{ $ppmp ? route('ppmps.show', $ppmp) : route('ppmps.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit">Save as Draft</x-button>
            </div>
        </div>

        {{-- Mobile sticky save bar --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 lg:hidden">
            <div class="mx-auto flex max-w-3xl items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total ABC</p>
                    <p class="truncate text-base font-bold text-slate-900 dark:text-white">₱{{ number_format($this->totalAbc, 2) }}</p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <x-button href="{{ $ppmp ? route('ppmps.show', $ppmp) : route('ppmps.index') }}" variant="secondary" size="sm">Cancel</x-button>
                    <x-button type="submit" size="sm">Save</x-button>
                </div>
            </div>
        </div>
    </form>
</div>
