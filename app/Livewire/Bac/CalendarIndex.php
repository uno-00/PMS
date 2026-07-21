<?php

namespace App\Livewire\Bac;

use App\Models\Bac\BacCalendarEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Phase 7 "Calendar View": month-grid visualization of every scheduled BAC activity. */
class CalendarIndex extends Component
{
    #[Url]
    public string $month;

    public function mount(): void
    {
        Gate::authorize('viewAny', BacCalendarEvent::class);
        $this->month ??= now()->format('Y-m');
    }

    public function delete(string $id): void
    {
        $event = BacCalendarEvent::query()->findOrFail($id);
        Gate::authorize('delete', $event);
        $event->delete();
        session()->flash('status', 'Calendar event removed.');
    }

    public function previousMonth(): void
    {
        $this->month = Carbon::parse($this->month.'-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::parse($this->month.'-01')->addMonth()->format('Y-m');
    }

    public function today(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function render()
    {
        $cursor = Carbon::parse($this->month.'-01');
        $start = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $end = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $events = BacCalendarEvent::query()
            ->with('procurement')
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn ($event) => $event->scheduled_at->toDateString());

        $weeks = collect();
        $week = collect();
        $day = $start->copy();
        while ($day->lte($end)) {
            $week->push($day->copy());
            if ($week->count() === 7) {
                $weeks->push($week);
                $week = collect();
            }
            $day->addDay();
        }

        $upcoming = BacCalendarEvent::query()->with('procurement')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        return view('livewire.bac.calendar-index', [
            'cursor' => $cursor,
            'weeks' => $weeks,
            'events' => $events,
            'upcoming' => $upcoming,
        ])->layout('components.layouts.app', ['title' => 'BAC Calendar']);
    }
}
