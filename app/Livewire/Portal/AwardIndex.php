<?php

namespace App\Livewire\Portal;

use App\Enums\NoticeOfAwardStatus;
use App\Services\Bac\AwardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AwardIndex extends Component
{
    public function respond(string $noaId, bool $accept, AwardService $service): void
    {
        $noa = Auth::user()->bidder->noticeOfAwards()->findOrFail($noaId);

        abort_unless($noa->status === NoticeOfAwardStatus::Awarded, 422, 'This award has already been responded to.');

        $service->respond($noa, $accept);

        session()->flash('status', $accept ? 'Award accepted. The agency will proceed with the Notice to Proceed / Purchase Order.' : 'Award declined.');
    }

    public function render()
    {
        $awards = Auth::user()->bidder->noticeOfAwards()
            ->with(['procurement.noticeToProceed', 'procurement.purchaseOrders'])
            ->latest()
            ->paginate(10);

        return view('livewire.portal.award-index', compact('awards'))
            ->layout('components.layouts.portal', ['title' => 'My Awards']);
    }
}
