<?php

namespace App\Livewire\AuditTrail;

use App\Livewire\Concerns\InteractsWithTableFilters;
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
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $logName = '';

    #[Url]
    public string $event = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterUser = '';

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
        $this->resetTableFilters([
            'logName',
            'event',
            'search',
            'filterUser',
        ]);
    }

    public function render()
    {
        $query = Activity::query()
            ->with('causer');

        $this->applyExactFilter($query, 'log_name', $this->logName);
        $this->applyExactFilter($query, 'event', $this->event);
        $this->applyLikeFilter($query, 'description', $this->search);

        if ($this->filterUser !== '') {
            $query->whereHas('causer', fn ($q) => $q->where('name', 'like', '%'.$this->filterUser.'%'));
        }

        $this->applyCreatedAtFilter($query);

        $activities = $query->latest()->paginate(25);

        return view('livewire.audit-trail.index', [
            'activities' => $activities,
            'logNames' => Activity::query()->select('log_name')->distinct()->orderBy('log_name')->pluck('log_name'),
        ])->layout('components.layouts.app', ['title' => 'Audit Trail']);
    }
}
