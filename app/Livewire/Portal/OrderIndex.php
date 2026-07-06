<?php

namespace App\Livewire\Portal;

use App\Services\Supplier\BidDocumentOrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class OrderIndex extends Component
{
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

    public function render()
    {
        $orders = Auth::user()->bidder->bidDocumentOrders()->with('procurement')->latest()->paginate(10);

        return view('livewire.portal.order-index', compact('orders'))
            ->layout('components.layouts.portal', ['title' => 'Bid Document Orders']);
    }
}
