<?php

namespace App\Livewire\Payment;

use App\Models\Procurement\Payment;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PaymentShow extends Component
{
    public Payment $payment;

    public function mount(Payment $payment): void
    {
        Gate::authorize('view', $payment);
        $this->payment = $payment->load(['purchaseOrder.bidder', 'processedBy']);
    }

    public function render()
    {
        return view('livewire.payment.payment-show')
            ->layout('components.layouts.app', [
                'title' => 'Payment · '.($this->payment->or_no ?: 'Record'),
            ]);
    }
}
