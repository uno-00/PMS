<div>
    <x-page-header title="BAC Calendar" subtitle="Pre-procurement conferences, pre-bid conferences, bid openings, post-qualification, NOA, NTP, contract signing, and PO issuance.">
        <x-slot:actions>
            <x-button wire:click="previousMonth" variant="secondary" size="sm">&larr;</x-button>
            <x-button wire:click="today" variant="secondary" size="sm">Today</x-button>
            <x-button wire:click="nextMonth" variant="secondary" size="sm">&rarr;</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <x-card class="lg:col-span-3">
            <h3 class="mb-4 text-center text-lg font-semibold text-slate-800 dark:text-white">{{ $cursor->format('F Y') }}</h3>
            <div class="grid grid-cols-7 gap-px overflow-hidden rounded-lg bg-slate-200 text-xs dark:bg-slate-800">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                    <div class="bg-slate-50 py-2 text-center font-semibold text-slate-500 dark:bg-slate-900 dark:text-slate-400">{{ $d }}</div>
                @endforeach

                @foreach($weeks as $week)
                    @foreach($week as $day)
                        @php $dayEvents = $events->get($day->toDateString(), collect()); @endphp
                        <div class="min-h-[90px] bg-white p-1.5 dark:bg-slate-900 {{ $day->month !== $cursor->month ? 'opacity-40' : '' }}">
                            <p class="text-right text-xs {{ $day->isToday() ? 'font-bold text-primary-600' : 'text-slate-400' }}">{{ $day->day }}</p>
                            <div class="mt-1 space-y-1">
                                @foreach($dayEvents->take(3) as $event)
                                    <a href="{{ $event->procurement ? route('procurements.show', $event->procurement) : '#' }}"
                                       class="block truncate rounded bg-primary-50 px-1.5 py-0.5 text-[10px] font-medium text-primary-700 dark:bg-primary-900/30 dark:text-primary-300"
                                       title="{{ $event->title }}">
                                        {{ $event->scheduled_at->format('g:ia') }} {{ $event->title }}
                                    </a>
                                @endforeach
                                @if($dayEvents->count() > 3)
                                    <p class="text-[10px] text-slate-400">+{{ $dayEvents->count() - 3 }} more</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </x-card>

        <x-card title="Upcoming Activities">
            @forelse($upcoming as $event)
                <div class="border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $event->typeLabel() }}</p>
                    <p class="text-xs text-slate-400">{{ $event->scheduled_at->format('M d, Y g:ia') }}</p>
                    @if($event->procurement)
                        <a href="{{ route('procurements.show', $event->procurement) }}" class="text-xs font-medium text-primary-600 hover:underline">{{ $event->procurement->title }}</a>
                    @endif
                </div>
            @empty
                <x-empty-state icon="calendar" title="No upcoming activities" />
            @endforelse
        </x-card>
    </div>
</div>
