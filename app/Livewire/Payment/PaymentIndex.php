<?php

namespace App\Livewire\Payment;

use App\Models\Procurement\Payment;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PaymentIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('payment.view');
    }

    public function render()
    {
        $payments = Payment::query()
            ->with(['purchaseOrder.bidder', 'processedBy'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.payment.payment-index', [
            'payments' => $payments,
            'totalProcessed' => (clone $payments)->getCollection()->sum('amount'),
        ])->layout('components.layouts.app', ['title' => 'Payment Monitoring']);
    }
}
