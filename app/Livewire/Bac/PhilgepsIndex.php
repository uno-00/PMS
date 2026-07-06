<?php

namespace App\Livewire\Bac;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Bac\PhilgepsPosting;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PhilgepsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('philgeps.view');
    }

    public function render()
    {
        $postings = PhilgepsPosting::query()
            ->with(['procurement.modeOfProcurement', 'postedBy'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('posting_date')
            ->paginate(15);

        return view('livewire.bac.philgeps-index', [
            'postings' => $postings,
            'statuses' => PhilgepsPostingStatus::cases(),
        ])->layout('components.layouts.app', ['title' => 'PhilGEPS Postings']);
    }
}
