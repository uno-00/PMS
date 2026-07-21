<?php

namespace App\Livewire\PurchaseRequest;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\Division;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseRequestIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterPrNo = '';

    #[Url]
    public string $filterPurpose = '';

    #[Url]
    public string $filterDivisionId = '';

    #[Url]
    public string $filterAmount = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', PurchaseRequest::class);
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterPrNo',
            'filterPurpose',
            'filterDivisionId',
            'filterAmount',
            'status',
        ]);
    }

    public function render()
    {
        $user = Auth::user();

        $query = PurchaseRequest::query()
            ->with(['division', 'fiscalYear', 'ppmp'])
            ->when(! $user->hasAnyRole(['Super Admin', 'System Admin', 'Planning Officer', 'Budget Officer', 'HOPE', 'Internal Auditor', 'Viewer', 'Accounting Officer']), function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            });

        $this->applyLikeFilter($query, 'pr_no', $this->filterPrNo);
        $this->applyLikeFilter($query, 'purpose', $this->filterPurpose);
        $this->applyExactFilter($query, 'division_id', $this->filterDivisionId);
        $this->applyAmountFilter($query, 'total_amount', $this->filterAmount);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $prs = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.purchase-request.purchase-request-index', [
            'prs' => $prs,
            'divisions' => Division::query()->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Purchase Requests']);
    }
}
