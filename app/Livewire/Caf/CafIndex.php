<?php

namespace App\Livewire\Caf;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CafIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterCafNo = '';

    #[Url]
    public string $filterPurchaseRequest = '';

    #[Url]
    public string $filterFundSource = '';

    #[Url]
    public string $filterAmount = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', CertificateOfAvailabilityOfFunds::class);
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterCafNo',
            'filterPurchaseRequest',
            'filterFundSource',
            'filterAmount',
            'status',
        ]);
    }

    public function render()
    {
        $query = CertificateOfAvailabilityOfFunds::query()
            ->with(['purchaseRequest.division', 'fundSource']);

        $this->applyLikeFilter($query, 'caf_no', $this->filterCafNo);

        if ($this->filterPurchaseRequest !== '') {
            $query->whereHas('purchaseRequest', fn ($q) => $q->where('pr_no', 'like', '%'.$this->filterPurchaseRequest.'%'));
        }

        if ($this->filterFundSource !== '') {
            $query->whereHas('fundSource', fn ($q) => $q->where('name', 'like', '%'.$this->filterFundSource.'%'));
        }

        $this->applyAmountFilter($query, 'amount', $this->filterAmount);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $cafs = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.caf.caf-index', compact('cafs'))
            ->layout('components.layouts.app', ['title' => 'Certificate of Availability of Funds']);
    }
}
