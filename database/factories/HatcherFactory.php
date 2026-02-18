<?php

namespace Database\Factories;

use App\Models\Hatcher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hatcher>
 */
class HatcherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hatcherName' => fake()->words(3, true) . ' Hatcher-' . fake()->numberBetween(1, 10),
            'isActive' => true,
            'creationDate' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
