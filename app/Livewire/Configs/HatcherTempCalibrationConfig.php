<?php

namespace App\Livewire\Configs;

class HatcherTempCalibrationConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man'       => 'required|integer|exists:users,id',
            'form.shift'              => 'required|string|in:1st Shift,2nd Shift,3rd Shift',
            'form.time_started'       => ['required', 'date_format:H:i'],
            'form.hatcher'            => 'required|integer|exists:hatcher-machines,id',

            'form.machine_temp'       => 'required|numeric',
            'form.calibrator_temp'    => 'required|numeric',
            'form.reading_photos.*'   => 'nullable|image|max:1024',
            'form.humidity_reading'   => 'required|numeric',
            'form.humidity_photos.*'  => 'nullable|image|max:1024',

            'form.approver'           => 'required|string|max:255',
            'form.time_finished'      => ['required', 'date_format:H:i'],
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                          => 'Please fill in this field.',
            'form.time_started.date_format'     => 'Please select a valid time.',
            'form.time_finished.date_format'    => 'Please select a valid time.',
            'form.hatchery_man.required'        => 'Please select a hatchery man.',
            'form.hatchery_man.exists'          => 'Please select a valid hatchery man.',
            'form.hatcher.required'             => 'Please select a hatcher.',
            'form.hatcher.exists'               => 'Please select a valid hatcher.',
            'form.machine_temp.numeric'         => 'Machine temperature must be a number.',
            'form.calibrator_temp.numeric'      => 'Calibrator temperature must be a number.',
            'form.humidity_reading.numeric'     => 'Humidity reading must be a number.',
            'in'                                => 'Please select a valid option.',
            'integer'                           => 'Please enter a valid number.',
            'string'                            => 'Please enter valid text.',
            'max'                               => 'File size must not exceed 1MB.',
            'image'                             => 'Please upload a valid image.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Hatcher Temperature Calibration';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man'     => '',
            'shift'            => '',
            'time_started'     => '',
            'hatcher'          => '',

            'machine_temp'     => '',
            'calibrator_temp'  => '',
            'reading_photos'   => [],
            'humidity_reading' => '',
            'humidity_photos'  => [],

            'approver'         => '',
            'time_finished'    => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'shift', 'time_started', 'hatcher'],
            2 => ['machine_temp', 'calibrator_temp', 'reading_photos', 'humidity_reading', 'humidity_photos'],
            3 => ['approver', 'time_finished'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
