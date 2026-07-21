@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $isEdit = isset($posting) && $posting && $posting->exists;
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header :title="$isEdit ? 'Edit PhilGEPS Posting' : 'New Manual PhilGEPS Posting'" subtitle="Record a procurement case posted manually on the PhilGEPS website.">
        @if($isEdit)
            <x-slot:actions>
                <x-button href="{{ route('philgeps.show', $posting) }}" variant="secondary" size="sm">Back</x-button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <form wire:submit="save" class="space-y-5 sm:space-y-6">
        <x-card title="Posting details">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="procurement_id" class="{{ $label }}">Procurement Case</label>
                    @if($isEdit)
                        <p class="mt-1.5 text-slate-700 dark:text-slate-200">{{ $posting->procurement?->title ?? '—' }}</p>
                    @else
                        <select id="procurement_id" wire:model="procurement_id" class="{{ $field }}">
                            <option value="">Select a procurement case (without an existing posting)…</option>
                            @foreach($procurements as $procurement)
                                <option value="{{ $procurement->id }}">{{ $procurement->case_no }} — {{ $procurement->title }}</option>
                            @endforeach
                        </select>
                        @error('procurement_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>

                <div>
                    <label for="reference_no" class="{{ $label }}">PhilGEPS Reference No.</label>
                    <input id="reference_no" type="text" wire:model="reference_no" class="{{ $field }}" placeholder="e.g. PG-123456" />
                    @error('reference_no') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="posting_date" class="{{ $label }}">Posting Date</label>
                    <input id="posting_date" type="date" wire:model="posting_date" class="{{ $field }}" />
                    @error('posting_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="closing_date" class="{{ $label }}">Closing Date</label>
                    <input id="closing_date" type="date" wire:model="closing_date" class="{{ $field }}" />
                    @error('closing_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="remarks" class="{{ $label }}">Remarks</label>
                    <textarea id="remarks" wire:model="remarks" rows="3" class="{{ $field }}"></textarea>
                    @error('remarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-card>

        <div class="flex justify-end gap-2">
            @if($isEdit)
                <x-button href="{{ route('philgeps.show', $posting) }}" variant="secondary">Cancel</x-button>
            @endif
            <x-button type="submit">Save Posting</x-button>
        </div>
    </form>
</div>
