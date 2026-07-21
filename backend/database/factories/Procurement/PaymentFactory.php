<?php

namespace Database\Factories\Procurement;

use App\Models\Procurement\Payment;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'or_no' => 'OR-'.fake()->unique()->numerify('######'),
            'amount' => fake()->randomFloat(2, 1000, 500000),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'method' => fake()->randomElement(Payment::METHODS),
            'status' => 'pending',
        ];
    }

    public function released(): static
    {
        return $this->state(fn () => ['status' => 'released']);
    }
}
