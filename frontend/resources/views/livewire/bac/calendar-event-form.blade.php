@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $isEdit = isset($calendarEvent) && $calendarEvent && $calendarEvent->exists;
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header :title="$isEdit ? 'Edit Calendar Event' : 'New Calendar Event'" subtitle="Schedule a BAC activity on the procurement calendar.">
        @if($isEdit)
            <x-slot:actions>
                <x-button href="{{ route('bac-calendar.show', $calendarEvent) }}" variant="secondary" size="sm">Back</x-button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <form wire:submit="save" class="space-y-5 sm:space-y-6">
        <x-card title="Event details">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="activity_type" class="{{ $label }}">Activity Type</label>
                    <select id="activity_type" wire:model="activity_type" class="{{ $field }}">
                        @foreach($types as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('activity_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="{{ $label }}">Status</label>
                    <select id="status" wire:model="status" class="{{ $field }}">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="{{ $label }}">Title</label>
                    <input id="title" type="text" wire:model="title" class="{{ $field }}" placeholder="e.g. Bid Opening — Office Supplies" />
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="scheduled_at" class="{{ $label }}">Scheduled Date & Time</label>
                    <input id="scheduled_at" type="datetime-local" wire:model="scheduled_at" class="{{ $field }}" />
                    @error('scheduled_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="venue" class="{{ $label }}">Venue</label>
                    <input id="venue" type="text" wire:model="venue" class="{{ $field }}" placeholder="e.g. BAC Conference Room" />
                    @error('venue') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="procurement_id" class="{{ $label }}">Linked Procurement (optional)</label>
                    <select id="procurement_id" wire:model="procurement_id" class="{{ $field }}">
                        <option value="">None (standalone event)</option>
                        @foreach($procurements as $procurement)
                            <option value="{{ $procurement->id }}">{{ $procurement->title }}</option>
                        @endforeach
                    </select>
                    @error('procurement_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                <x-button href="{{ route('bac-calendar.show', $calendarEvent) }}" variant="secondary">Cancel</x-button>
            @endif
            <x-button type="submit">Save Event</x-button>
        </div>
    </form>
</div>
