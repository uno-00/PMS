<?php

namespace Database\Factories\Settings;

use App\Models\Settings\FiscalYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalYear>
 */
class FiscalYearFactory extends Factory
{
    public function definition(): array
    {
        // Use a future range unlikely to collide with seeded fiscal years.
        $year = fake()->unique()->numberBetween(2031, 2099);

        return [
            'year' => $year,
            'start_date' => "{$year}-01-01",
            'end_date' => "{$year}-12-31",
            'status' => 'active',
            'is_current' => false,
            'total_gaa_amount' => 0,
        ];
    }
}
