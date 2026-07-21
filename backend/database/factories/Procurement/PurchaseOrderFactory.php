<?php

namespace Database\Factories\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10000, 500000);

        return [
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'total_amount' => $subtotal,
            'delivery_date' => fake()->dateTimeBetween('now', '+30 days'),
            'delivery_place' => fake()->address(),
            'status' => PurchaseOrderStatus::Draft,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => PurchaseOrderStatus::Approved, 'approved_at' => now()]);
    }
}
