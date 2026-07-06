<?php

namespace App\Livewire\Portal;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class BidIndex extends Component
{
    public function render()
    {
        $bids = Auth::user()->bidder->bidSubmissions()
            ->with(['procurement', 'evaluation'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.portal.bid-index', compact('bids'))
            ->layout('components.layouts.portal', ['title' => 'My Bids']);
    }
}
