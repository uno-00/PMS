<?php

namespace App\Livewire\Portal;

use App\Livewire\Concerns\InteractsWithTableFilters;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BidIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterBidNo = '';

    #[Url]
    public string $filterProcurement = '';

    #[Url]
    public string $filterVersion = '';

    #[Url]
    public string $status = '';

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterBidNo',
            'filterProcurement',
            'filterVersion',
            'status',
        ]);
    }

    public function render()
    {
        $query = Auth::user()->bidder->bidSubmissions()
            ->with(['procurement', 'evaluation']);

        $this->applyLikeFilter($query, 'bid_no', $this->filterBidNo);

        if ($this->filterProcurement !== '') {
            $query->whereHas('procurement', fn ($q) => $q->where('title', 'like', '%'.$this->filterProcurement.'%'));
        }

        if ($this->filterVersion !== '') {
            $query->where('version', $this->filterVersion);
        }

        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $bids = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.portal.bid-index', compact('bids'))
            ->layout('components.layouts.portal', ['title' => 'My Bids']);
    }
}
