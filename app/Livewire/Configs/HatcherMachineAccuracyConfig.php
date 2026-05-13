<?php

namespace App\Livewire\Configs;

class HatcherMachineAccuracyConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man'    => 'required|array|min:1',
            'form.hatchery_man.*'  => 'integer|exists:users,id',
            'form.cellphone_number' => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'form.date_submitted'  => 'required|date',
            'form.time_of_reading' => ['required', 'date_format:H:i'],
            'form.shift'           => 'required|string|in:1st Shift,2nd Shift,3rd Shift',

            'form.hatchers'                             => 'required|array|min:1',
            'form.hatchers.*.set_point_temp'            => 'required|numeric',
            'form.hatchers.*.display_temp'              => 'required|numeric',
            'form.hatchers.*.calibrator'                => 'required|numeric',
            'form.hatchers.*.humidity_set_point'        => 'required|numeric',
            'form.hatchers.*.humidity_machine_reading'  => 'required|numeric',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                                                => 'Please fill in this field.',
            'form.cellphone_number.regex'                             => 'Please enter a valid Philippine cellphone number (09XXXXXXXXX or +639XXXXXXXXX).',
            'form.time_of_reading.date_format'                        => 'Please select a valid time.',
            'form.hatchery_man.required'                              => 'Please select at least one hatchery man.',
            'form.hatchery_man.min'                                   => 'Please select at least one hatchery man.',
            'form.hatchers.required'                                  => 'Hatcher data is required.',
            'form.hatchers.min'                                       => 'At least one hatcher is required.',
            'form.hatchers.*.set_point_temp.required'                 => 'Please enter the set point temperature.',
            'form.hatchers.*.set_point_temp.numeric'                  => 'Set Point Temperature must be a number.',
            'form.hatchers.*.display_temp.required'                   => 'Please enter the display temperature.',
            'form.hatchers.*.display_temp.numeric'                    => 'Display Temp must be a number.',
            'form.hatchers.*.calibrator.required'                     => 'Please enter the calibrator reading.',
            'form.hatchers.*.calibrator.numeric'                      => 'Calibrator must be a number.',
            'form.hatchers.*.humidity_set_point.required'             => 'Please enter the humidity set point.',
            'form.hatchers.*.humidity_set_point.numeric'              => 'Humidity Set Point must be a number.',
            'form.hatchers.*.humidity_machine_reading.required'       => 'Please enter the humidity machine reading.',
            'form.hatchers.*.humidity_machine_reading.numeric'        => 'Humidity Machine Reading must be a number.',
            'in'                                                      => 'Please select a valid option.',
            'integer'                                                 => 'Please enter a valid number.',
            'string'                                                  => 'Please enter valid text.',
            'date'                                                    => 'Please enter a valid date.',
            'max'                                                     => 'File size must not exceed 1MB.',
            'image'                                                   => 'Please upload a valid image.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Hatcher Machine Accuracy Temperature Checking';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man'    => [],
            'cellphone_number' => '',
            'date_submitted'  => '',
            'time_of_reading' => '',
            'shift'           => '',
            'hatchers'        => [],
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'cellphone_number', 'date_submitted', 'time_of_reading', 'shift'],
            2 => ['hatchers'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
