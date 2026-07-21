<?php

namespace App\Livewire\Planning;

use App\Enums\MarketScopingStatus;
use App\Models\Planning\MarketScoping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MarketScopingShow extends Component
{
    public MarketScoping $marketScoping;

    public function mount(MarketScoping $marketScoping): void
    {
        Gate::authorize('view', $marketScoping);
        $this->marketScoping = $marketScoping->load(['division', 'fiscalYear', 'preparedBy', 'approvedBy']);
    }

    public function approve(): void
    {
        Gate::authorize('approve', $this->marketScoping);

        $this->marketScoping->update([
            'status' => MarketScopingStatus::Approved,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        session()->flash('status', 'Market Scoping Checklist approved.');
        $this->marketScoping->refresh();
    }

    public function render()
    {
        return view('livewire.planning.market-scoping-show')
            ->layout('components.layouts.app', ['title' => $this->marketScoping->project_name]);
    }
}
