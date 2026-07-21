<?php

namespace App\Livewire\Portal;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Services\Supplier\BidDocumentOrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OrderIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterOrderNo = '';

    #[Url]
    public string $filterProcurement = '';

    #[Url]
    public string $filterAmount = '';

    #[Url]
    public string $filterOrNo = '';

    #[Url]
    public string $filterPaymentStatus = '';

    /**
     * No real payment gateway is integrated; this simulates an over-the-counter
     * / online payment confirmation by generating an OR number, matching the
     * "Payment Status" + "Receipt" requirements of Phase 10.
     */
    public function pay(string $orderId, BidDocumentOrderService $service): void
    {
        $order = Auth::user()->bidder->bidDocumentOrders()->findOrFail($orderId);

        $service->markPaid($order, 'OR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)));

        session()->flash('status', 'Payment recorded. You may now download the bidding documents and submit your bid.');
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterOrderNo',
            'filterProcurement',
            'filterAmount',
            'filterOrNo',
            'filterPaymentStatus',
        ]);
    }

    public function render()
    {
        $query = Auth::user()->bidder->bidDocumentOrders()
            ->with('procurement');

        $this->applyLikeFilter($query, 'order_no', $this->filterOrderNo);

        if ($this->filterProcurement !== '') {
            $query->whereHas('procurement', fn ($q) => $q->where('title', 'like', '%'.$this->filterProcurement.'%'));
        }

        $this->applyAmountFilter($query, 'amount', $this->filterAmount);
        $this->applyLikeFilter($query, 'or_no', $this->filterOrNo);
        $this->applyExactFilter($query, 'payment_status', $this->filterPaymentStatus);
        $this->applyCreatedAtFilter($query);

        $orders = $query->latest()->paginate(10);

        return view('livewire.portal.order-index', compact('orders'))
            ->layout('components.layouts.portal', ['title' => 'Bid Document Orders']);
    }
}
