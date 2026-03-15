<?php

namespace App\Livewire\Configs;

class EntranceDamperSpacingConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man'    => 'required|integer|exists:users,id',
            'form.shift'           => 'required|string|in:1st Shift,2nd Shift,3rd Shift',
            'form.time_of_reading' => ['required', 'date_format:H:i'],
            'form.incubator'       => 'required|integer|exists:incubator-machines,id',

            'form.measurement'       => 'required|numeric',
            'form.measurement_photo' => 'nullable',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                         => 'Please fill in this field.',
            'form.time_of_reading.date_format' => 'Please select a valid time.',
            'form.hatchery_man.required'       => 'Please select a hatchery man.',
            'form.hatchery_man.exists'         => 'Please select a valid hatchery man.',
            'form.incubator.required'          => 'Please select an incubator.',
            'form.incubator.exists'            => 'Please select a valid incubator.',
            'form.measurement.numeric'         => 'Measurement must be a number.',
            'in'                               => 'Please select a valid option.',
            'integer'                          => 'Please enter a valid number.',
            'date'                             => 'Please enter a valid date.',
            'max'                              => 'File size must not exceed 1MB.',
            'image'                            => 'Please upload a valid image.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Entrance Damper Spacing Monitoring';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man'    => '',
            'shift'           => '',
            'time_of_reading' => '',
            'incubator'       => '',

            'measurement'       => '',
            'measurement_photo' => [],
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'shift', 'time_of_reading', 'incubator'],
            2 => ['measurement', 'measurement_photo'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
