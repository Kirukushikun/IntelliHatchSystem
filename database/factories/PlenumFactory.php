<?php

namespace Database\Factories;

use App\Models\Plenum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plenum>
 */
class PlenumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plenumName' => fake()->words(3, true) . ' Plenum-' . fake()->numberBetween(1, 10),
            'isActive' => true,
            'creationDate' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
