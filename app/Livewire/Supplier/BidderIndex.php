<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier\Bidder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class BidderIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('bidder.view');
    }

    public function render()
    {
        $bidders = Bidder::query()
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->where('company_name', 'like', "%{$this->search}%"))
            ->orderBy('company_name')
            ->paginate(15);

        return view('livewire.supplier.bidder-index', [
            'bidders' => $bidders,
            'statusCounts' => Bidder::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ])->layout('components.layouts.app', ['title' => 'Bidders / Suppliers']);
    }
}
