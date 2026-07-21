<div>
    <x-page-header :title="$calendarEvent->title" :subtitle="$calendarEvent->typeLabel()">
        <x-slot:actions>
            <x-status-badge :status="new \App\Support\SimpleStatus($calendarEvent->status)" class="!text-sm" />
            @can('update', $calendarEvent)
                <x-button href="{{ route('bac-calendar.edit', $calendarEvent) }}" variant="secondary">Edit</x-button>
            @endcan
            <x-button href="{{ route('bac-calendar.index') }}" variant="secondary">Back to Calendar</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Scheduled" :value="$calendarEvent->scheduled_at?->format('M d, Y g:ia') ?? '—'" icon="calendar" />
        <x-stat-card label="Venue" :value="$calendarEvent->venue ?: '—'" icon="map-pin" accent="indigo" />
        <x-stat-card label="Status" :value="ucfirst($calendarEvent->status)" icon="flag" accent="amber" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Event">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">Activity Type</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $calendarEvent->typeLabel() }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Title</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $calendarEvent->title }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Created by</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $calendarEvent->creator?->name ?? '—' }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Linked Procurement">
            <dl class="space-y-3 text-sm">
                @if($calendarEvent->procurement)
                    <div><dt class="text-xs uppercase text-slate-400">Procurement</dt><dd class="mt-0.5"><a href="{{ route('procurements.show', $calendarEvent->procurement) }}" class="font-medium text-primary-700 hover:underline dark:text-primary-400">{{ $calendarEvent->procurement->title }}</a></dd></div>
                @else
                    <div><dd class="text-slate-500">This is a standalone event (not linked to a procurement case).</dd></div>
                @endif
                @if($calendarEvent->remarks)
                    <div><dt class="text-xs uppercase text-slate-400">Remarks</dt><dd class="mt-0.5 whitespace-pre-wrap text-slate-700 dark:text-slate-200">{{ $calendarEvent->remarks }}</dd></div>
                @endif
            </dl>
        </x-card>
    </div>
</div>
