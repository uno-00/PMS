<?php

namespace App\Livewire\Planning;

use App\Models\Planning\MarketScoping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MarketScopingIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', MarketScoping::class);
    }

    public function render()
    {
        $user = Auth::user();

        $records = MarketScoping::query()
            ->with(['division', 'fiscalYear', 'preparedBy'])
            ->when(! $user->hasAnyRole(['Super Admin', 'System Admin', 'Planning Officer', 'Budget Officer', 'HOPE', 'Internal Auditor', 'Viewer']), function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.planning.market-scoping-index', compact('records'))
            ->layout('components.layouts.app', ['title' => 'Market Scoping']);
    }
}
