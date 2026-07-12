<?php

namespace App\Livewire\Bac;

use App\Models\Bac\BacCalendarEvent;
use App\Models\Bac\Procurement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CalendarEventForm extends Component
{
    public ?BacCalendarEvent $calendarEvent = null;

    public string $activity_type = 'pre_bid_conference';

    public string $title = '';

    public string $scheduled_at = '';

    public string $venue = '';

    public string $remarks = '';

    public string $status = 'scheduled';

    public string $procurement_id = '';

    public function mount(?BacCalendarEvent $calendarEvent = null): void
    {
        if ($calendarEvent && $calendarEvent->exists) {
            Gate::authorize('update', $calendarEvent);
            $this->calendarEvent = $calendarEvent;
            $this->fillFromModel($calendarEvent);
        } else {
            Gate::authorize('create', BacCalendarEvent::class);
            $this->scheduled_at = now()->addDay()->format('Y-m-d\TH:i');
        }
    }

    protected function fillFromModel(BacCalendarEvent $event): void
    {
        $this->activity_type = $event->activity_type;
        $this->title = $event->title;
        $this->scheduled_at = $event->scheduled_at?->format('Y-m-d\TH:i') ?? '';
        $this->venue = $event->venue ?? '';
        $this->remarks = $event->remarks ?? '';
        $this->status = $event->status ?? 'scheduled';
        $this->procurement_id = $event->procurement_id ?? '';
    }

    protected function rules(): array
    {
        return [
            'activity_type' => ['required', 'in:'.implode(',', array_keys(BacCalendarEvent::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', BacCalendarEvent::STATUSES)],
            'procurement_id' => ['nullable', 'exists:procurements,id'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $payload = [
            'activity_type' => $this->activity_type,
            'title' => $this->title,
            'scheduled_at' => $this->scheduled_at,
            'venue' => $this->venue ?: null,
            'remarks' => $this->remarks ?: null,
            'status' => $this->status,
            'procurement_id' => $this->procurement_id ?: null,
        ];

        if ($this->calendarEvent && $this->calendarEvent->exists) {
            $this->calendarEvent->update($payload);
            $record = $this->calendarEvent;
        } else {
            $record = BacCalendarEvent::query()->create(array_merge($payload, [
                'created_by' => Auth::id(),
            ]));
        }

        session()->flash('status', 'Calendar event saved.');
        $this->redirect(route('bac-calendar.show', $record), navigate: false);
    }

    public function render()
    {
        return view('livewire.bac.calendar-event-form', [
            'types' => BacCalendarEvent::TYPES,
            'statuses' => BacCalendarEvent::STATUSES,
            'procurements' => Procurement::query()->orderByDesc('created_at')->limit(200)->get(),
        ])->layout('components.layouts.app', [
            'title' => $this->calendarEvent && $this->calendarEvent->exists ? 'Edit Calendar Event' : 'New Calendar Event',
        ]);
    }
}
