<?php

namespace App\Livewire\Caf;

use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Services\Procurement\CafService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CafShow extends Component
{
    public CertificateOfAvailabilityOfFunds $caf;

    public function mount(CertificateOfAvailabilityOfFunds $caf): void
    {
        Gate::authorize('view', $caf);
        $this->caf = $caf;
    }

    public function certify(CafService $service): void
    {
        Gate::authorize('certify', $this->caf);
        $service->certify($this->caf, Auth::user());
        session()->flash('status', 'CAF certified.');
        $this->caf->refresh();
    }

    public function approve(CafService $service): void
    {
        Gate::authorize('approve', $this->caf);
        $service->approve($this->caf, Auth::user());
        session()->flash('status', 'CAF approved.');
        $this->caf->refresh();
    }

    public function render()
    {
        $history = $this->caf->workflowHistories()->with('performedBy')->latest('performed_at')->get();

        return view('livewire.caf.caf-show', compact('history'))
            ->layout('components.layouts.app', ['title' => $this->caf->caf_no]);
    }
}
