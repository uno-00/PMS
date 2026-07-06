<?php

namespace App\Livewire\Bac;

use App\Models\Bac\BidSubmission;
use App\Models\Bac\Procurement;
use App\Services\Bac\BiddingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

/** Phase 13: BAC TWG bid evaluation matrix — compliance, ranking, remarks, recommendation. */
#[Layout('components.layouts.app')]
class BidEvaluationShow extends Component
{
    public Procurement $procurement;

    public ?string $editingBidId = null;

    public array $compliance = [
        'eligibility' => false,
        'technical' => false,
        'financial' => false,
    ];

    public string $score = '';

    public string $rank = '';

    public string $remarks = '';

    public string $recommendation = 'recommended';

    public function mount(Procurement $procurement): void
    {
        Gate::authorize('bid-evaluation.view');
        $this->procurement = $procurement;
    }

    public function edit(string $bidId): void
    {
        Gate::authorize('bid-evaluation.evaluate');

        $bid = BidSubmission::with('evaluation')->findOrFail($bidId);
        $this->editingBidId = $bidId;

        if ($bid->evaluation) {
            $this->compliance = array_merge($this->compliance, (array) $bid->evaluation->compliance);
            $this->score = (string) $bid->evaluation->score;
            $this->rank = (string) $bid->evaluation->rank;
            $this->remarks = (string) $bid->evaluation->remarks;
            $this->recommendation = (string) $bid->evaluation->recommendation;
        } else {
            $this->reset('score', 'rank', 'remarks');
            $this->recommendation = 'recommended';
        }
    }

    public function cancel(): void
    {
        $this->reset('editingBidId', 'score', 'rank', 'remarks');
    }

    public function save(BiddingService $service): void
    {
        Gate::authorize('bid-evaluation.evaluate');
        $this->validate([
            'score' => 'required|numeric|min:0|max:100',
            'rank' => 'nullable|integer|min:1',
            'recommendation' => 'required|in:recommended,not_recommended',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $bid = BidSubmission::findOrFail($this->editingBidId);

        $service->evaluate(
            $bid,
            Auth::user(),
            $this->compliance,
            (float) $this->score,
            $this->rank !== '' ? (int) $this->rank : null,
            $this->recommendation,
            $this->remarks ?: null
        );

        $this->cancel();
        session()->flash('status', 'Evaluation saved.');
    }

    public function render()
    {
        $bids = $this->procurement->bidSubmissions()->with(['bidder', 'evaluation'])->orderBy('created_at')->get();

        return view('livewire.bac.bid-evaluation-show', compact('bids'))
            ->layout('components.layouts.app', ['title' => 'Bid Evaluation — '.$this->procurement->case_no]);
    }
}
