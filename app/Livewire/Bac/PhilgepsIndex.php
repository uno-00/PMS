<?php

namespace App\Livewire\Bac;

use App\Enums\PhilgepsPostingStatus;
use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Bac\PhilgepsPosting;
use App\Services\Bac\PhilgepsPostingService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PhilgepsIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterReferenceNo = '';

    #[Url]
    public string $filterProcurement = '';

    #[Url]
    public string $filterMode = '';

    #[Url]
    public string $filterType = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', PhilgepsPosting::class);
    }

    public function delete(string $id, PhilgepsPostingService $service): void
    {
        $posting = PhilgepsPosting::query()->findOrFail($id);
        Gate::authorize('delete', $posting);
        $service->delete($posting);
        session()->flash('status', 'PhilGEPS posting removed.');
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterReferenceNo',
            'filterProcurement',
            'filterMode',
            'filterType',
            'status',
        ]);
    }

    public function render()
    {
        $query = PhilgepsPosting::query()
            ->with(['procurement.modeOfProcurement', 'postedBy']);

        $this->applyLikeFilter($query, 'reference_no', $this->filterReferenceNo);

        if ($this->filterProcurement !== '') {
            $query->whereHas('procurement', fn ($q) => $q->where('title', 'like', '%'.$this->filterProcurement.'%'));
        }

        if ($this->filterMode !== '') {
            $query->whereHas('procurement.modeOfProcurement', fn ($q) => $q->where('name', 'like', '%'.$this->filterMode.'%'));
        }

        if ($this->filterType !== '') {
            $query->where('is_manual', $this->filterType === 'manual');
        }

        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $postings = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.bac.philgeps-index', [
            'postings' => $postings,
            'statuses' => PhilgepsPostingStatus::cases(),
        ])->layout('components.layouts.app', ['title' => 'PhilGEPS Postings']);
    }
}
