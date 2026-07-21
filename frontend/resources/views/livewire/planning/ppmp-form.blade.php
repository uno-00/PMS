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
                        Break down items under MOOE and Capital Outlay. {{ count($items) }} {{ Str::plural('row', count($items)) }} &middot; Total ABC ₱{{ number_format($this->totalAbc, 2) }}
                        @if($this->projectProposalTotalCost !== null)
                            &middot; Project cost ₱{{ number_format($this->projectProposalTotalCost, 2) }}
                            &middot; Remaining <span class="{{ $this->remainingFund < 0 ? 'font-semibold text-red-600 dark:text-red-400' : 'font-semibold text-emerald-700 dark:text-emerald-400' }}">₱{{ number_format($this->remainingFund, 2) }}</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Mobile item switcher --}}
            @if(count($items) > 1)
                <div class="mb-4 -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 lg:hidden" role="tablist" aria-label="Procurement items">
                    @foreach($items as $index => $item)
                        @php
                            $lineAbc = \App\Livewire\Planning\PpmpForm::lineAbc($item);
                            $sectionLabel = $expenseClassOptions[$item['expense_class'] ?? 'mooe'] ?? 'MOOE';
                        @endphp
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
                            <span class="block whitespace-nowrap text-xs opacity-70">{{ $sectionLabel }}</span>
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

            <div class="space-y-8">
                @foreach($expenseClassOptions as $expenseClassValue => $expenseClassLabel)
                    @php
                        $sectionRows = $this->itemsForExpenseClass($expenseClassValue);
                        $sectionAbc = $this->sectionAbc($expenseClassValue);
                    @endphp
                    <section
                        wire:key="expense-section-{{ $expenseClassValue }}"
                        x-data="{ sectionOpen: true }"
                        class="rounded-xl border border-slate-200 dark:border-slate-800"
                    >
                        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-800/50">
                            <button type="button" @click="sectionOpen = !sectionOpen" class="flex min-w-0 flex-1 items-start gap-2 text-left" :aria-expanded="sectionOpen">
                                <svg
                                    class="mt-0.5 h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
                                    :class="{ 'rotate-90': sectionOpen }"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $expenseClassLabel }}</h4>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {{ count($sectionRows) }} {{ Str::plural('row', count($sectionRows)) }}
                                        @if($sectionAbc > 0)
                                            &middot; Subtotal ₱{{ number_format($sectionAbc, 2) }}
                                        @endif
                                    </p>
                                </div>
                            </button>
                            <x-button type="button" wire:click="addItem('{{ $expenseClassValue }}')" variant="secondary" size="sm" class="w-full sm:w-auto" @click.stop>
                                + Add {{ $expenseClassLabel }} row
                            </x-button>
                        </div>

                        <div x-show="sectionOpen" x-transition>
                        @if($sectionRows === [])
                            <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                No {{ $expenseClassLabel }} items yet. Click &ldquo;Add {{ $expenseClassLabel }} row&rdquo; to add one.
                            </p>
                        @else
                            <div class="space-y-4 p-4 lg:space-y-5">
                                @foreach($sectionRows as $sectionIndex => $row)
                                    @php
                                        $index = $row['index'];
                                        $item = $row['item'];
                                        $lineAbc = \App\Livewire\Planning\PpmpForm::lineAbc($item);
                                    @endphp
                                    @include('livewire.planning.partials.ppmp-item-fields', [
                                        'index' => $index,
                                        'sectionIndex' => $sectionIndex,
                                        'item' => $item,
                                        'lineAbc' => $lineAbc,
                                        'sectionLabel' => $expenseClassLabel,
                                        'canRemove' => count($items) > 1,
                                        'showMobileNav' => count($items) > 1,
                                        'isLastInAll' => $index === count($items) - 1,
                                        'remainingFund' => $this->remainingFund,
                                        'projectProposalTotalCost' => $this->projectProposalTotalCost,
                                        'defaultOpen' => $sectionIndex === 0,
                                        'expenseClassOptions' => $expenseClassOptions,
                                        'projectTypeOptions' => $projectTypeOptions,
                                    ])
                                @endforeach
                            </div>
                        @endif
                        </div>
                    </section>
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
