<?php

namespace Database\Factories\Bac;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhilgepsPosting>
 */
class PhilgepsPostingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'procurement_id' => Procurement::factory()->withPr(),
            'reference_no' => 'PG-'.fake()->unique()->numerify('######'),
            'posting_date' => fake()->dateTimeBetween('-10 days', 'now'),
            'closing_date' => fake()->dateTimeBetween('now', '+15 days'),
            'status' => PhilgepsPostingStatus::Published,
            'is_manual' => true,
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
