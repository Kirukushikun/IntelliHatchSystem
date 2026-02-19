<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class HatcherySullairFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $formType = FormType::where('form_name', 'Hatchery Sullair Air Compressor Weekly PMS Checklist')->first();

        return [
            'form_type_id' => $formType?->id,
            'form_inputs' => function () {
                $sullairOptions = [
                    'Sullair 1 (Inside Incubation Area)',
                    'Sullair 2 (Maintenance Area)',
                ];

                $phCellphoneNumber = '09' . str_pad((string) $this->faker->numberBetween(0, 999999999), 9, '0', STR_PAD_LEFT);

                return [
                    'hatchery_man' => User::factory()->create(['user_type' => 1])->id,
                    'cellphone_number' => $phCellphoneNumber,
                    'sullair_number' => $this->faker->randomElement($sullairOptions),

                    'actual_psi_reading' => $this->faker->numberBetween(60, 90) . ' psi',
                    'actual_temperature_reading' => $this->faker->numberBetween(150, 200) . ' F',
                    'actual_volt_reading' => $this->faker->numberBetween(200, 240) . ' V',
                    'actual_ampere_reading' => $this->faker->numberBetween(8, 15) . ' A',

                    'status_wiring_lugs_control' => $this->faker->randomElement(['Good Condition', 'With Minimal Damage', 'For Replacement']),
                    'status_solenoid_valve' => $this->faker->randomElement(['Good Condition', 'With Minimal Damage', 'For Replacement']),
                    'status_fan_motor' => $this->faker->randomElement(['Good Condition', 'With Minimal Damage', 'For Replacement']),

                    'status_hose' => $this->faker->randomElement(['No Leak', 'With Leak for Repair', 'With Leak for Replacement']),
                    'actual_oil_level_status' => $this->faker->randomElement(['Above or On Its Level Requirement', 'For Refill']),
                    'tension_belt_status' => $this->faker->randomElement(['Good Condition', 'For Replacement']),

                    'status_water_filter' => $this->faker->randomElement(['Good Condition', 'For Replacement']),
                    'air_pipe_status' => $this->faker->randomElement(['No Any Leak', 'With Leak For Repair or Replacement']),
                    'air_dryer_status' => $this->faker->randomElement(['Clean and Good Status', 'For Repair and Replacement']),

                    'inspected_by' => $this->faker->name(),
                ];
            },
            'date_submitted' => fake()->dateTimeBetween('-3 months', 'now'),
            'uploaded_by' => User::factory()->create(['user_type' => 1])->id,
        ];
    }
}
