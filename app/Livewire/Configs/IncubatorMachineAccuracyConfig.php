<?php

namespace App\Livewire\Configs;

class IncubatorMachineAccuracyConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man'    => 'required|integer|exists:users,id',
            'form.mobile_number'   => ['required', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'form.date_submitted'  => 'required|date',
            'form.time_of_reading' => ['required', 'date_format:H:i'],
            'form.shift'           => 'required|string|in:1st Shift,2nd Shift,3rd Shift',
            'form.incubator'       => 'required|integer|exists:incubator-machines,id',

            'form.display_temp'    => 'required|numeric',
            'form.calibrator'      => 'required|numeric',

            'form.accuracy_photos.*' => 'image|max:1024',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                         => 'Please fill in this field.',
            'form.mobile_number.regex'         => 'Please enter a valid Philippine mobile number (09XXXXXXXXX or +639XXXXXXXXX).',
            'form.time_of_reading.date_format' => 'Please select a valid time.',
            'form.hatchery_man.required'       => 'Please select a hatchery man.',
            'form.hatchery_man.exists'         => 'Please select a valid hatchery man.',
            'form.incubator.required'          => 'Please select an incubator.',
            'form.incubator.exists'            => 'Please select a valid incubator.',
            'form.display_temp.numeric'        => 'Display Temp must be a number.',
            'form.calibrator.numeric'          => 'Calibrator must be a number.',
            'in'                               => 'Please select a valid option.',
            'integer'                          => 'Please enter a valid number.',
            'string'                           => 'Please enter valid text.',
            'date'                             => 'Please enter a valid date.',
            'max'                              => 'File size must not exceed 1MB.',
            'image'                            => 'Please upload a valid image.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Incubator Machine Accuracy Temperature Checking';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man'    => '',
            'mobile_number'   => '',
            'date_submitted'  => '',
            'time_of_reading' => '',
            'shift'           => '',
            'incubator'       => '',

            'display_temp'    => '',
            'calibrator'      => '',

            'accuracy_photos' => [],
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'mobile_number', 'date_submitted', 'time_of_reading', 'shift', 'incubator'],
            2 => ['display_temp', 'calibrator', 'accuracy_photos'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
