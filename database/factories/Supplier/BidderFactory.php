<?php

namespace Database\Factories\Supplier;

use App\Models\Supplier\Bidder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bidder>
 */
class BidderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'business_type' => fake()->randomElement(['sole_proprietorship', 'partnership', 'corporation', 'cooperative']),
            'philgeps_registration_no' => 'PG-'.fake()->unique()->numerify('######'),
            'philgeps_registration_expiry' => fake()->dateTimeBetween('+3 months', '+2 years'),
            'tin' => fake()->numerify('###-###-###-###'),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'status' => 'pending',
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['status' => 'verified']);
    }
}
