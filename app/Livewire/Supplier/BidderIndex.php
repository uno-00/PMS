<?php

namespace App\Livewire\Supplier;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Supplier\Bidder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class BidderIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterCompany = '';

    #[Url]
    public string $filterContactPerson = '';

    #[Url]
    public string $filterEmail = '';

    #[Url]
    public string $filterPhilgepsNo = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('bidder.view');
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterCompany',
            'filterContactPerson',
            'filterEmail',
            'filterPhilgepsNo',
            'status',
        ]);
    }

    public function render()
    {
        $query = Bidder::query();

        $this->applyLikeFilter($query, 'company_name', $this->filterCompany);
        $this->applyLikeFilter($query, 'contact_person', $this->filterContactPerson);
        $this->applyLikeFilter($query, 'email', $this->filterEmail);
        $this->applyLikeFilter($query, 'philgeps_registration_no', $this->filterPhilgepsNo);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $bidders = $query->orderBy('company_name')->paginate(15);

        return view('livewire.supplier.bidder-index', [
            'bidders' => $bidders,
            'statusCounts' => Bidder::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ])->layout('components.layouts.app', ['title' => 'Bidders / Suppliers']);
    }
}
