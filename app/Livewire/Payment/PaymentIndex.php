<?php

namespace App\Livewire\Payment;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Procurement\Payment;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PaymentIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterOrNo = '';

    #[Url]
    public string $filterPoNo = '';

    #[Url]
    public string $filterSupplier = '';

    #[Url]
    public string $filterAmount = '';

    #[Url]
    public string $filterMethod = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Payment::class);
    }

    public function delete(string $id): void
    {
        $payment = Payment::query()->findOrFail($id);
        Gate::authorize('delete', $payment);
        $payment->delete();
        session()->flash('status', 'Payment record removed.');
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterOrNo',
            'filterPoNo',
            'filterSupplier',
            'filterAmount',
            'filterMethod',
            'status',
        ]);
    }

    public function render()
    {
        $query = Payment::query()
            ->with(['purchaseOrder.bidder', 'processedBy']);

        $this->applyLikeFilter($query, 'or_no', $this->filterOrNo);

        if ($this->filterPoNo !== '') {
            $query->whereHas('purchaseOrder', fn ($q) => $q->where('po_no', 'like', '%'.$this->filterPoNo.'%'));
        }

        if ($this->filterSupplier !== '') {
            $query->whereHas('purchaseOrder.bidder', fn ($q) => $q->where('company_name', 'like', '%'.$this->filterSupplier.'%'));
        }

        $this->applyAmountFilter($query, 'amount', $this->filterAmount);
        $this->applyExactFilter($query, 'method', $this->filterMethod);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $payments = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.payment.payment-index', [
            'payments' => $payments,
            'totalProcessed' => (clone $payments)->getCollection()->sum('amount'),
        ])->layout('components.layouts.app', ['title' => 'Payment Monitoring']);
    }
}
