<?php

namespace App\Livewire\Configs;

class IncubatorEntranceTempConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man'       => 'required|array|min:1',
            'form.hatchery_man.*'     => 'integer|exists:users,id',
            'form.shift'              => 'required|string|in:1st Shift,2nd Shift,3rd Shift',
            'form.time_of_check'      => ['required', 'date_format:H:i'],
            'form.incubator'          => 'required|integer|exists:incubator-machines,id',

            'form.set_point_temp'     => 'required|string',
            'form.set_point_humidity' => 'required|string',
            'form.entrance_temp_left'  => 'required|string',
            'form.entrance_temp_right' => 'required|string',
            'form.entrance_photo.*'   => 'image|max:1024',

            'form.temp_adjustment_notes'   => 'required|string',
            'form.temp_adjustment_photo.*' => 'image|max:1024',
            'form.time_finished'           => ['required', 'date_format:H:i'],
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                              => 'Please fill in this field.',
            'string'                                => 'Please enter valid text.',
            'in'                                    => 'Please select a valid option.',
            'integer'                               => 'Please enter a valid number.',
            'max'                                   => 'File size must not exceed 1MB.',
            'image'                                 => 'Please upload a valid image.',
            'form.hatchery_man.required'            => 'Please select at least one hatcheryman.',
            'form.hatchery_man.min'                 => 'Please select at least one hatcheryman.',
            'form.shift.required'                   => 'Please select a shift.',
            'form.time_of_check.required'           => 'Please enter the time of checking.',
            'form.time_of_check.date_format'        => 'Please select a valid time.',
            'form.incubator.required'               => 'Please select an incubator.',
            'form.incubator.exists'                 => 'Please select a valid incubator.',
            'form.set_point_temp.required'     => 'Please enter the set point temperature.',
            'form.set_point_humidity.required' => 'Please enter the set point humidity.',
            'form.entrance_temp_left.required'  => 'Please enter the left entrance temperature reading.',
            'form.entrance_temp_right.required' => 'Please enter the right entrance temperature reading.',
            'form.temp_adjustment_notes.required'   => 'Please enter the temperature adjustment notes.',
            'form.time_finished.required'           => 'Please enter the time finished.',
            'form.time_finished.date_format'        => 'Please select a valid time.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Incubator Entrance Temperature Monitoring';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man'       => [],
            'shift'              => '',
            'time_of_check'      => '',
            'incubator'          => '',

            'set_point_temp'     => '',
            'set_point_humidity' => '',
            'entrance_temp_left'  => '',
            'entrance_temp_right' => '',
            'entrance_photo'     => [],

            'temp_adjustment_notes'  => '',
            'temp_adjustment_photo'  => [],
            'time_finished'          => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'shift', 'time_of_check', 'incubator'],
            2 => [
                'set_point_temp', 'set_point_humidity',
                'entrance_temp_left', 'entrance_temp_right', 'entrance_photo',
            ],
            3 => ['temp_adjustment_notes', 'temp_adjustment_photo', 'time_finished'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
