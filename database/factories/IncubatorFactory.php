<?php

namespace Database\Factories;

use App\Models\Incubator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incubator>
 */
class IncubatorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'incubatorName' => fake()->words(3, true) . ' Incubator-' . fake()->numberBetween(1, 10),
            'isActive' => true,
            'creationDate' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
