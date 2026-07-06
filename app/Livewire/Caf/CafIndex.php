<?php

namespace App\Livewire\Caf;

use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CafIndex extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', CertificateOfAvailabilityOfFunds::class);
    }

    public function render()
    {
        $cafs = CertificateOfAvailabilityOfFunds::query()
            ->with(['purchaseRequest.division', 'fundSource'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.caf.caf-index', compact('cafs'))
            ->layout('components.layouts.app', ['title' => 'Certificate of Availability of Funds']);
    }
}
