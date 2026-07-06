<?php

namespace App\Livewire\Bac;

use App\Models\Bac\Procurement;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProcurementIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('bac-calendar.view');
    }

    public function render()
    {
        $procurements = Procurement::query()
            ->with(['purchaseRequest.division', 'modeOfProcurement'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.bac.procurement-index', compact('procurements'))
            ->layout('components.layouts.app', ['title' => 'Procurement Cases']);
    }
}
