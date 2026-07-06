<?php

namespace App\Livewire\PurchaseRequest;

use App\Models\Procurement\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PurchaseRequestIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', PurchaseRequest::class);
    }

    public function render()
    {
        $user = Auth::user();

        $prs = PurchaseRequest::query()
            ->with(['division', 'fiscalYear', 'ppmp'])
            ->when(! $user->hasAnyRole(['Super Admin', 'System Admin', 'Planning Officer', 'Budget Officer', 'HOPE', 'Internal Auditor', 'Viewer', 'Accounting Officer']), function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.purchase-request.purchase-request-index', compact('prs'))
            ->layout('components.layouts.app', ['title' => 'Purchase Requests']);
    }
}
