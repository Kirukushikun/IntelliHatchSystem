<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'form_type_id' => FormType::factory(),
            'form_inputs' => [
                'field1' => $this->faker->sentence,
                'field2' => $this->faker->randomNumber(2),
                'field3' => $this->faker->boolean,
            ],
            'date_submitted' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'uploaded_by' => User::factory(),
        ];
    }
}
