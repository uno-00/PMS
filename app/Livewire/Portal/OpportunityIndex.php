<?php

namespace App\Livewire\Portal;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Bac\PhilgepsPosting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
class OpportunityIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = 'open';

    public function render()
    {
        $postings = PhilgepsPosting::query()
            ->with('procurement.modeOfProcurement')
            ->when($this->status === 'open', fn ($q) => $q->where('status', PhilgepsPostingStatus::Published)->where('closing_date', '>=', now()))
            ->when($this->status === 'closed', fn ($q) => $q->whereIn('status', [PhilgepsPostingStatus::Closed, PhilgepsPostingStatus::Cancelled])->orWhere('closing_date', '<', now()))
            ->when($this->search, fn ($q) => $q->whereHas('procurement', fn ($p) => $p->where('title', 'like', "%{$this->search}%")->orWhere('case_no', 'like', "%{$this->search}%")))
            ->orderByDesc('posting_date')
            ->paginate(10);

        return view('livewire.portal.opportunity-index', compact('postings'))
            ->layout('components.layouts.portal', ['title' => 'Open Opportunities']);
    }
}
