<?php

namespace App\Livewire\Configs;

class PlenumTempHumidityConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatcheryman'                           => 'required|integer|exists:users,id',
            'form.date'                                  => 'required|date',
            'form.shift'                                 => 'required|string|in:1st Shift,2nd Shift,3rd Shift',
            'form.time'                                  => 'required|string',

            'form.plenum_incubator_1_5'                  => 'required|string',
            'form.plenum_incubator_1_5_photos.*'         => 'image|max:1024',
            'form.plenum_incubator_6_10'                 => 'required|string',
            'form.plenum_incubator_6_10_photos.*'        => 'image|max:1024',

            'form.plenum_hatcher_1_5'                    => 'required|string',
            'form.plenum_hatcher_1_5_photos.*'           => 'image|max:1024',
            'form.plenum_hatcher_6_10'                   => 'required|string',
            'form.plenum_hatcher_6_10_photos.*'          => 'image|max:1024',

            'form.random_humidity_entrance_dumper'       => 'required|string',
            'form.random_humidity_top_baggy'             => 'required|string',
            'form.random_entrance_dumper_incubator'      => 'required|string',

            'form.aircon_count'                          => 'required|string',
            'form.humidity_incubator_hallway'            => 'required|numeric',
            'form.humidity_outside'                      => 'required|numeric',
            'form.weather_condition'                     => 'required|string',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                                             => 'Please fill in this field.',
            'string'                                               => 'Please enter valid text.',
            'numeric'                                              => 'Please enter a valid number.',
            'date'                                                 => 'Please enter a valid date.',
            'in'                                                   => 'Please select a valid option.',
            'max'                                                  => 'File size must not exceed 1MB.',
            'image'                                                => 'Please upload a valid image.',
            'form.hatcheryman.required'                           => 'Please select a hatcheryman.',
            'form.hatcheryman.exists'                             => 'Please select a valid hatcheryman.',
            'form.date.required'                                   => 'Please select a date.',
            'form.shift.required'                                  => 'Please select a shift.',
            'form.time.required'                                   => 'Please select a time.',
            'form.plenum_incubator_1_5.required'                  => 'Please enter the reading for Incubator 1–5.',
            'form.plenum_incubator_6_10.required'                 => 'Please enter the reading for Incubator 6–10.',
            'form.plenum_hatcher_1_5.required'                    => 'Please enter the reading for Hatcher 1–5.',
            'form.plenum_hatcher_6_10.required'                   => 'Please enter the reading for Hatcher 6–10.',
            'form.random_humidity_entrance_dumper.required'       => 'Please enter the humidity reading.',
            'form.random_humidity_top_baggy.required'             => 'Please enter the humidity reading.',
            'form.random_entrance_dumper_incubator.required'      => 'Please enter the reading.',
            'form.aircon_count.required'                          => 'Please select the number of aircons open.',
            'form.humidity_incubator_hallway.required'            => 'Please enter the humidity at incubator hallway.',
            'form.humidity_outside.required'                      => 'Please enter the humidity outside.',
            'form.weather_condition.required'                     => 'Please select the weather condition.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Plenum Temperature and Humidity Monitoring';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatcheryman'                      => '',
            'date'                             => '',
            'shift'                            => '',
            'time'                             => '',

            'plenum_incubator_1_5'             => '',
            'plenum_incubator_1_5_photos'      => [],
            'plenum_incubator_6_10'            => '',
            'plenum_incubator_6_10_photos'     => [],

            'plenum_hatcher_1_5'               => '',
            'plenum_hatcher_1_5_photos'        => [],
            'plenum_hatcher_6_10'              => '',
            'plenum_hatcher_6_10_photos'       => [],

            'random_humidity_entrance_dumper'  => '',
            'random_humidity_top_baggy'        => '',
            'random_entrance_dumper_incubator' => '',

            'aircon_count'                     => '',
            'humidity_incubator_hallway'       => '',
            'humidity_outside'                 => '',
            'weather_condition'                => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatcheryman', 'date', 'shift', 'time'],
            2 => [
                'plenum_incubator_1_5', 'plenum_incubator_1_5_photos',
                'plenum_incubator_6_10', 'plenum_incubator_6_10_photos',
                'plenum_hatcher_1_5', 'plenum_hatcher_1_5_photos',
                'plenum_hatcher_6_10', 'plenum_hatcher_6_10_photos',
            ],
            3 => [
                'random_humidity_entrance_dumper',
                'random_humidity_top_baggy',
                'random_entrance_dumper_incubator',
                'aircon_count',
                'humidity_incubator_hallway',
                'humidity_outside',
                'weather_condition',
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
