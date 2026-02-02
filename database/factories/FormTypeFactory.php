<?php

namespace Database\Factories;

use App\Models\FormType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormType>
 */
class FormTypeFactory extends Factory
{
    protected $model = FormType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $colors = ['blue', 'green', 'red', 'purple', 'amber', 'gray'];
        
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence,
            'color' => $this->faker->randomElement($colors),
        ];
    }
}
