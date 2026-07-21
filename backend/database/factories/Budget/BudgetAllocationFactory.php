<?php

namespace Database\Factories\Budget;

use App\Models\Budget\BudgetAllocation;
use App\Models\Settings\FiscalYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetAllocation>
 */
class BudgetAllocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fiscal_year_id' => FiscalYear::factory(),
            'level' => 'department',
            'allocated_amount' => fake()->randomFloat(2, 100000, 5000000),
            'utilized_amount' => 0,
            'remarks' => fake()->optional()->sentence(),
        ];
    }

    public function leaf(): static
    {
        return $this->state(fn () => ['level' => 'cost_center']);
    }
}
