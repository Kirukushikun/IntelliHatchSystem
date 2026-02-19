<?php

namespace App\Livewire\Configs;

class HatcherySullairConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man' => 'required|integer|exists:users,id',
            'form.cellphone_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'form.sullair_number' => 'required|string|in:Sullair 1 (Inside Incubation Area),Sullair 2 (Maintenance Area)',

            'form.actual_psi_reading' => 'required|numeric|between:60,90',
            'form.actual_temperature_reading' => 'required|numeric|between:150,200',
            'form.actual_volt_reading' => 'required|string',
            'form.actual_ampere_reading' => 'required|string',

            'form.status_wiring_lugs_control' => 'required|string|in:Good Condition,With Minimal Damage,For Replacement',
            'form.status_solenoid_valve' => 'required|string|in:Good Condition,With Minimal Damage,For Replacement',
            'form.status_fan_motor' => 'required|string|in:Good Condition,With Minimal Damage,For Replacement',

            'form.status_hose' => 'required|string|in:No Leak,With Leak for Repair,With Leak for Replacement',
            'form.actual_oil_level_status' => 'required|string|in:Above or On Its Level Requirement,For Refill',
            'form.tension_belt_status' => 'required|string|in:Good Condition,For Replacement',

            'form.status_water_filter' => 'required|string|in:Good Condition,For Replacement',
            'form.air_pipe_status' => 'required|string|in:No Any Leak,With Leak For Repair or Replacement',
            'form.air_dryer_status' => 'required|string|in:Clean and Good Status,For Repair and Replacement',
            'form.inspected_by' => 'required|string|min:2',

            'form.actual_psi_temperature_photos.*' => 'image|max:1024',
            'form.actual_volt_photos.*' => 'image|max:1024',
            'form.actual_ampere_photos.*' => 'image|max:1024',

            'form.status_wiring_lugs_control_photos.*' => 'image|max:1024',
            'form.status_solenoid_valve_photos.*' => 'image|max:1024',
            'form.status_fan_motor_photos.*' => 'image|max:1024',

            'form.status_hose_photos.*' => 'image|max:1024',
            'form.actual_oil_level_status_photos.*' => 'image|max:1024',
            'form.tension_belt_status_photos.*' => 'image|max:1024',

            'form.status_water_filter_photos.*' => 'image|max:1024',
            'form.air_pipe_status_photos.*' => 'image|max:1024',
            'form.air_dryer_status_photos.*' => 'image|max:1024',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required' => 'Please fill in this field.',
            'form.cellphone_number.regex' => 'Please enter a valid Philippine cellphone number (09XXXXXXXXX or +639XXXXXXXXX).',
            'form.actual_psi_reading.numeric' => 'Actual PSI Reading must be a number only (no text).',
            'form.actual_psi_reading.between' => 'Actual PSI Reading must be between 60 and 90.',
            'form.actual_temperature_reading.numeric' => 'Actual Temperature Reading must be a number only (no text).',
            'form.actual_temperature_reading.between' => 'Actual Temperature Reading must be between 150 and 200.',
            'in' => 'Please select a valid option.',
            'integer' => 'Please enter a valid number.',
            'string' => 'Please enter valid text.',
            'min' => 'Please enter a valid value.',
            'max' => 'File size must not exceed 1MB.',
            'image' => 'Please upload a valid image.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Hatchery Sullair Air Compressor Weekly PMS Checklist';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man' => '',
            'cellphone_number' => '',
            'sullair_number' => '',

            'actual_psi_reading' => '',
            'actual_temperature_reading' => '',
            'actual_volt_reading' => '',
            'actual_ampere_reading' => '',

            'actual_psi_temperature_photos' => [],
            'actual_volt_photos' => [],
            'actual_ampere_photos' => [],

            'status_wiring_lugs_control' => '',
            'status_solenoid_valve' => '',
            'status_fan_motor' => '',

            'status_wiring_lugs_control_photos' => [],
            'status_solenoid_valve_photos' => [],
            'status_fan_motor_photos' => [],

            'status_hose' => '',
            'actual_oil_level_status' => '',
            'tension_belt_status' => '',

            'status_hose_photos' => [],
            'actual_oil_level_status_photos' => [],
            'tension_belt_status_photos' => [],

            'status_water_filter' => '',
            'air_pipe_status' => '',
            'air_dryer_status' => '',
            'inspected_by' => '',

            'status_water_filter_photos' => [],
            'air_pipe_status_photos' => [],
            'air_dryer_status_photos' => [],
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'cellphone_number', 'sullair_number'],
            2 => [
                'actual_psi_reading',
                'actual_temperature_reading',
                'actual_psi_temperature_photos',
                'actual_volt_reading',
                'actual_volt_photos',
                'actual_ampere_reading',
                'actual_ampere_photos',
            ],
            3 => [
                'status_wiring_lugs_control',
                'status_wiring_lugs_control_photos',
                'status_solenoid_valve',
                'status_solenoid_valve_photos',
                'status_fan_motor',
                'status_fan_motor_photos',
            ],
            4 => [
                'status_hose',
                'status_hose_photos',
                'actual_oil_level_status',
                'actual_oil_level_status_photos',
                'tension_belt_status',
                'tension_belt_status_photos',
            ],
            5 => [
                'status_water_filter',
                'status_water_filter_photos',
                'air_pipe_status',
                'air_pipe_status_photos',
                'air_dryer_status',
                'air_dryer_status_photos',
                'inspected_by',
            ],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
