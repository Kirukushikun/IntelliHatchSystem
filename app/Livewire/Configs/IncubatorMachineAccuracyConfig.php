<?php

namespace App\Livewire\Configs;

class IncubatorMachineAccuracyConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man'    => 'required|array|min:1',
            'form.hatchery_man.*'  => 'integer|exists:users,id',
            'form.mobile_number'   => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'form.date_submitted'  => 'required|date',
            'form.time_of_reading' => ['required', 'date_format:H:i'],
            'form.shift'           => 'required|string|in:1st Shift,2nd Shift,3rd Shift',

            'form.incubators'                => 'required|array|min:1',
            'form.incubators.*.display_temp' => 'required|numeric',
            'form.incubators.*.calibrator'   => 'required|numeric',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                                    => 'Please fill in this field.',
            'form.mobile_number.regex'                    => 'Please enter a valid Philippine mobile number (09XXXXXXXXX or +639XXXXXXXXX).',
            'form.time_of_reading.date_format'            => 'Please select a valid time.',
            'form.hatchery_man.required'                  => 'Please select at least one hatchery man.',
            'form.hatchery_man.min'                       => 'Please select at least one hatchery man.',
            'form.incubators.required'                    => 'Incubator data is required.',
            'form.incubators.min'                         => 'At least one incubator is required.',
            'form.incubators.*.display_temp.required'     => 'Please enter the display temperature.',
            'form.incubators.*.display_temp.numeric'      => 'Display Temp must be a number.',
            'form.incubators.*.calibrator.required'       => 'Please enter the calibrator reading.',
            'form.incubators.*.calibrator.numeric'        => 'Calibrator must be a number.',
            'in'                                          => 'Please select a valid option.',
            'integer'                                     => 'Please enter a valid number.',
            'string'                                      => 'Please enter valid text.',
            'date'                                        => 'Please enter a valid date.',
            'max'                                         => 'File size must not exceed 1MB.',
            'image'                                       => 'Please upload a valid image.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Incubator Machine Accuracy Temperature Checking';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man'    => [],
            'mobile_number'   => '',
            'date_submitted'  => '',
            'time_of_reading' => '',
            'shift'           => '',
            'incubators'      => [],
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'mobile_number', 'date_submitted', 'time_of_reading', 'shift'],
            2 => ['incubators'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
