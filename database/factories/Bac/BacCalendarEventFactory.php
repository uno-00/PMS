<?php

namespace Database\Factories\Bac;

use App\Models\Bac\BacCalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BacCalendarEvent>
 */
class BacCalendarEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_type' => fake()->randomElement(array_keys(BacCalendarEvent::TYPES)),
            'title' => fake()->sentence(4),
            'scheduled_at' => fake()->dateTimeBetween('now', '+1 month'),
            'venue' => fake()->optional()->company(),
            'remarks' => fake()->optional()->sentence(),
            'status' => 'scheduled',
        ];
    }
}
