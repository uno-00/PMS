<?php

namespace App\Livewire\Portal;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Procurement\NoticeOfAward;
use App\Models\Supplier\Bidder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class Dashboard extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()->bidder, 403, 'No supplier profile linked to this account.');
    }

    public function render()
    {
        /** @var Bidder $bidder */
        $bidder = Auth::user()->bidder()->with(['bidDocumentOrders', 'bidSubmissions.procurement'])->first();

        $openOpportunities = PhilgepsPosting::query()
            ->with('procurement')
            ->where('status', PhilgepsPostingStatus::Published)
            ->where('closing_date', '>=', now())
            ->orderBy('closing_date')
            ->limit(5)
            ->get();

        $recentClarifications = $bidder->clarifications ?? collect();

        return view('livewire.portal.dashboard', [
            'bidder' => $bidder,
            'openOpportunities' => $openOpportunities,
            'activeBids' => $bidder->bidSubmissions()->latest()->limit(5)->get(),
            'pendingOrders' => $bidder->bidDocumentOrders()->where('payment_status', 'pending')->count(),
            'totalAwards' => NoticeOfAward::query()->where('bidder_id', $bidder->id)->count(),
        ])->layout('components.layouts.portal', ['title' => 'Supplier Dashboard']);
    }
}
