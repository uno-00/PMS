<?php

namespace App\Livewire\Payment;

use App\Models\Procurement\Payment;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PaymentForm extends Component
{
    public ?Payment $payment = null;

    public string $purchase_order_id = '';

    public string $or_no = '';

    public string $amount = '';

    public string $payment_date = '';

    public string $method = 'check';

    public string $status = 'pending';

    public function mount(?Payment $payment = null): void
    {
        if ($payment && $payment->exists) {
            Gate::authorize('update', $payment);
            $this->payment = $payment;
            $this->fillFromModel($payment);
        } else {
            Gate::authorize('create', Payment::class);
            $this->payment_date = now()->format('Y-m-d');
        }
    }

    protected function fillFromModel(Payment $payment): void
    {
        $this->purchase_order_id = $payment->purchase_order_id ?? '';
        $this->or_no = $payment->or_no ?? '';
        $this->amount = (string) $payment->amount;
        $this->payment_date = optional($payment->payment_date)->format('Y-m-d') ?? '';
        $this->method = $payment->method ?? 'check';
        $this->status = $payment->status ?? 'pending';
    }

    protected function rules(): array
    {
        return [
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'or_no' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'in:'.implode(',', Payment::METHODS)],
            'status' => ['required', 'in:'.implode(',', Payment::STATUSES)],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $payload = [
            'purchase_order_id' => $this->purchase_order_id,
            'or_no' => $this->or_no ?: null,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date ?: null,
            'method' => $this->method,
            'status' => $this->status,
        ];

        if ($this->payment && $this->payment->exists) {
            $this->payment->update($payload);
            $record = $this->payment;
        } else {
            $record = Payment::query()->create(array_merge($payload, [
                'processed_by' => Auth::id(),
            ]));
        }

        session()->flash('status', 'Payment record saved.');
        $this->redirect(route('payments.show', $record), navigate: false);
    }

    public function render()
    {
        return view('livewire.payment.payment-form', [
            'purchaseOrders' => PurchaseOrder::query()
                ->with('bidder')
                ->orderByDesc('created_at')
                ->limit(200)
                ->get(),
            'methods' => Payment::METHODS,
            'statuses' => Payment::STATUSES,
        ])->layout('components.layouts.app', [
            'title' => $this->payment && $this->payment->exists ? 'Edit Payment' : 'New Payment',
        ]);
    }
}
