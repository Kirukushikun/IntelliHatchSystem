<?php

namespace App\Livewire\Configs;

class WeeklyVoltAmpereConfig
{
    public static function getRules(): array
    {
        return [
            'form.maintenance_personnel'      => 'required|integer|exists:users,id',
            'form.date'                        => 'required|date',
            'form.time_started'                => 'required|string',
            'form.voltage_readings'            => 'required|string|max:1000',
            'form.ampere_readings'             => 'required|string|max:1000',
            'form.problem_corrective_action'   => 'required|string|max:2000',
            'form.time_finished'               => 'required|string',
            'form.voltage_ampere_photos.*'     => 'image|max:1024',
            'form.problem_photos.*'            => 'nullable|image|max:1024',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required' => 'This field is required.',
            'string'   => 'Please enter valid text.',
            'date'     => 'Please enter a valid date.',
            'integer'  => 'Please select a valid option.',
            'exists'   => 'Please select a valid option.',
            'max'      => 'This field exceeds the maximum allowed length.',
            'image'    => 'Please upload a valid image file.',
            'form.voltage_ampere_photos.*.max'   => 'Each photo must not exceed 1MB.',
            'form.problem_photos.*.max'          => 'Each photo must not exceed 1MB.',
            'form.maintenance_personnel.required' => 'Please select the maintenance personnel.',
            'form.date.required'                  => 'Please enter the date.',
            'form.time_started.required'          => 'Please enter the time started.',
            'form.voltage_readings.required'      => 'Please enter the voltage readings.',
            'form.ampere_readings.required'       => 'Please enter the ampere readings.',
            'form.problem_corrective_action.required' => 'Please enter the problem encountered and corrective action taken.',
            'form.time_finished.required'         => 'Please enter the time finished.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Weekly Voltage and Ampere Monitoring';
    }

    public static function defaultFormState(): array
    {
        return [
            'maintenance_personnel'    => '',
            'date'                     => '',
            'time_started'             => '',
            'voltage_readings'         => '',
            'ampere_readings'          => '',
            'voltage_ampere_photos'    => [],
            'problem_corrective_action' => '',
            'problem_photos'           => [],
            'time_finished'            => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['maintenance_personnel', 'date', 'time_started'],
            2 => [
                'voltage_readings',
                'ampere_readings',
                'voltage_ampere_photos',
                'problem_corrective_action',
                'problem_photos',
                'time_finished',
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
