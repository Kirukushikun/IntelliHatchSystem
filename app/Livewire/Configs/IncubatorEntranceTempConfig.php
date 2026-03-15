<?php

namespace App\Livewire\Configs;

class IncubatorEntranceTempConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man'       => 'required|integer|exists:users,id',
            'form.time_of_check'      => ['required', 'date_format:H:i'],
            'form.days_of_incubation' => 'required|string|in:Day 1 to 10,Day 10 to 12,Day 12 to 14,Day 14 to 18',
            'form.incubator'          => 'required|integer|exists:incubator-machines,id',

            'form.set_point_temp'     => 'required|string',
            'form.set_point_humidity' => 'required|string',
            'form.entrance_temp'      => 'required|string',
            'form.entrance_photo.*'   => 'image|max:1024',
            'form.baggy'              => 'required|string',
            'form.baggy_photo.*'      => 'image|max:1024',

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
            'form.hatchery_man.required'            => 'Please select a hatcheryman.',
            'form.hatchery_man.exists'              => 'Please select a valid hatcheryman.',
            'form.time_of_check.required'           => 'Please enter the time of checking.',
            'form.time_of_check.date_format'        => 'Please select a valid time.',
            'form.days_of_incubation.required'      => 'Please select the days of incubation.',
            'form.incubator.required'               => 'Please select an incubator.',
            'form.incubator.exists'                 => 'Please select a valid incubator.',
            'form.set_point_temp.required'     => 'Please enter the set point temperature.',
            'form.set_point_humidity.required' => 'Please enter the set point humidity.',
            'form.entrance_temp.required'      => 'Please enter the entrance temperature reading (left and right).',
            'form.baggy.required'              => 'Please enter the Baggy No. 2 temperature reading (left and right).',
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
            'hatchery_man'       => '',
            'time_of_check'      => '',
            'days_of_incubation' => '',
            'incubator'          => '',

            'set_point_temp'     => '',
            'set_point_humidity' => '',
            'entrance_temp'      => '',
            'entrance_photo'     => [],
            'baggy'              => '',
            'baggy_photo'        => [],

            'temp_adjustment_notes'  => '',
            'temp_adjustment_photo'  => [],
            'time_finished'          => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatchery_man', 'time_of_check', 'days_of_incubation', 'incubator'],
            2 => [
                'set_point_temp', 'set_point_humidity',
                'entrance_temp', 'entrance_photo',
                'baggy', 'baggy_photo',
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
