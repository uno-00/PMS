<?php

namespace App\Livewire\AuditTrail;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

/**
 * System-wide, filterable browser over every Spatie Activitylog entry
 * (HasAuditLog trait on every auditable model) plus workflow status
 * transitions, giving COA/Internal Audit a single place to review who
 * changed what, when, and the before/after values.
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $logName = '';

    #[Url]
    public string $event = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public ?string $activeActivityId = null;

    public function mount(): void
    {
        Gate::authorize('audit-trail.view');
    }

    public function toggleDetails(string $id): void
    {
        $this->activeActivityId = $this->activeActivityId === $id ? null : $id;
    }

    public function resetFilters(): void
    {
        $this->reset('logName', 'event', 'search', 'dateFrom', 'dateTo');
    }

    public function render()
    {
        $activities = Activity::query()
            ->with('causer')
            ->when($this->logName, fn ($q) => $q->where('log_name', $this->logName))
            ->when($this->event, fn ($q) => $q->where('event', $this->event))
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(25);

        return view('livewire.audit-trail.index', [
            'activities' => $activities,
            'logNames' => Activity::query()->select('log_name')->distinct()->orderBy('log_name')->pluck('log_name'),
        ])->layout('components.layouts.app', ['title' => 'Audit Trail']);
    }
}
