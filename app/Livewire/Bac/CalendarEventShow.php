<?php

namespace App\Livewire\Bac;

use App\Models\Bac\BacCalendarEvent;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class CalendarEventShow extends Component
{
    public BacCalendarEvent $calendarEvent;

    public function mount(BacCalendarEvent $calendarEvent): void
    {
        Gate::authorize('view', $calendarEvent);
        $this->calendarEvent = $calendarEvent->load(['procurement', 'creator']);
    }

    public function render()
    {
        return view('livewire.bac.calendar-event-show')
            ->layout('components.layouts.app', ['title' => $this->calendarEvent->title]);
    }
}
