<?php

namespace Tests\Feature;

use App\Models\Procurement\Payment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_accounting_officer_can_view_payments_index(): void
    {
        $user = User::query()->where('email', 'accounting.officer@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payment Monitoring');
    }

    public function test_end_user_cannot_access_payments_index(): void
    {
        $user = User::query()->where('email', 'end.user@pms.gov.ph')->firstOrFail();

        $this->actingAs($user)
            ->get(route('payments.index'))
            ->assertForbidden();
    }

    public function test_cashier_can_create_a_payment(): void
    {
        $actor = User::query()->where('email', 'cashier@pms.gov.ph')->firstOrFail();
        $po = PurchaseOrder::factory()->create();

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Payment\PaymentForm::class)
            ->set('purchase_order_id', $po->id)
            ->set('or_no', 'OR-100001')
            ->set('amount', 12500.50)
            ->set('payment_date', now()->format('Y-m-d'))
            ->set('method', 'check')
            ->set('status', 'pending')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'purchase_order_id' => $po->id,
            'or_no' => 'OR-100001',
            'amount' => 12500.50,
            'processed_by' => $actor->id,
        ]);
    }

    public function test_accounting_officer_can_edit_a_payment(): void
    {
        $actor = User::query()->where('email', 'accounting.officer@pms.gov.ph')->firstOrFail();
        $payment = Payment::factory()->create(['status' => 'pending']);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Payment\PaymentForm::class, ['payment' => $payment])
            ->set('amount', 9999.99)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('9999.99', $payment->fresh()->amount);
    }

    public function test_payment_cannot_be_deleted_once_released(): void
    {
        $actor = User::query()->where('email', 'accounting.officer@pms.gov.ph')->firstOrFail();
        $payment = Payment::factory()->create(['status' => 'released']);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Payment\PaymentIndex::class)
            ->call('delete', $payment->id)
            ->assertStatus(403);

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
    }

    public function test_accounting_officer_can_delete_a_pending_payment(): void
    {
        $actor = User::query()->where('email', 'accounting.officer@pms.gov.ph')->firstOrFail();
        $payment = Payment::factory()->create(['status' => 'pending']);

        Livewire::actingAs($actor)
            ->test(\App\Livewire\Payment\PaymentIndex::class)
            ->call('delete', $payment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
