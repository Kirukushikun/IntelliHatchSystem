<?php

namespace App\Livewire\Configs;

class PlenumTempHumidityConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatcheryman'                                  => 'required|integer|exists:users,id',
            'form.date'                                         => 'required|date',
            'form.shift'                                        => 'required|string|in:1st Shift,2nd Shift,3rd Shift',
            'form.time'                                         => 'required|string|in:6:00 AM,11:00 AM,4:00 PM,9:00 PM,2:00 AM',

            'form.incubator_readings'                           => 'required|array|min:1',
            'form.incubator_readings.*.incubator_id'            => 'required|integer|exists:incubator-machines,id|distinct',
            'form.incubator_readings.*.temperature'             => 'required|numeric',
            'form.incubator_readings.*.humidity'                => 'required|numeric',

            'form.hatcher_readings'                             => 'required|array|min:1',
            'form.hatcher_readings.*.hatcher_id'                => 'required|integer|exists:hatcher-machines,id|distinct',
            'form.hatcher_readings.*.temperature'               => 'required|numeric',
            'form.hatcher_readings.*.humidity'                  => 'required|numeric',

            'form.plenum_photos.*'                              => 'image|max:1024',

            'form.random_humidity_entrance_dumper'              => 'required|string',
            'form.random_humidity_top_baggy'                    => 'required|string',
            'form.random_entrance_dumper_incubator'             => 'required|string',

            'form.aircon_count'                                 => 'required|string',
            'form.humidity_incubator_hallway'                   => 'required|numeric',
            'form.humidity_outside'                             => 'required|numeric',
            'form.weather_condition'                            => 'required|string',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required'                                                          => 'Please fill in this field.',
            'string'                                                            => 'Please enter valid text.',
            'numeric'                                                           => 'Please enter a valid number.',
            'date'                                                              => 'Please enter a valid date.',
            'in'                                                                => 'Please select a valid option.',
            'max'                                                               => 'File size must not exceed 1MB.',
            'image'                                                             => 'Please upload a valid image.',
            'form.hatcheryman.required'                                        => 'Please select a hatcheryman.',
            'form.hatcheryman.exists'                                          => 'Please select a valid hatcheryman.',
            'form.date.required'                                                => 'Please select a date.',
            'form.shift.required'                                               => 'Please select a shift.',
            'form.time.required'                                                => 'Please select a time.',
            'form.incubator_readings.required'                                 => 'At least one incubator reading is required.',
            'form.incubator_readings.min'                                      => 'At least one incubator reading is required.',
            'form.incubator_readings.*.incubator_id.required'                  => 'Please select an incubator.',
            'form.incubator_readings.*.incubator_id.exists'                    => 'Please select a valid incubator.',
            'form.incubator_readings.*.incubator_id.distinct'                  => 'Each incubator can only be added once.',
            'form.incubator_readings.*.temperature.required'                   => 'Please enter the temperature.',
            'form.incubator_readings.*.temperature.numeric'                    => 'Temperature must be a number.',
            'form.incubator_readings.*.humidity.required'                      => 'Please enter the humidity.',
            'form.incubator_readings.*.humidity.numeric'                       => 'Humidity must be a number.',
            'form.hatcher_readings.required'                                   => 'At least one hatcher reading is required.',
            'form.hatcher_readings.min'                                        => 'At least one hatcher reading is required.',
            'form.hatcher_readings.*.hatcher_id.required'                      => 'Please select a hatcher machine.',
            'form.hatcher_readings.*.hatcher_id.exists'                        => 'Please select a valid hatcher machine.',
            'form.hatcher_readings.*.hatcher_id.distinct'                      => 'Each hatcher machine can only be added once.',
            'form.hatcher_readings.*.temperature.required'                     => 'Please enter the temperature.',
            'form.hatcher_readings.*.temperature.numeric'                      => 'Temperature must be a number.',
            'form.hatcher_readings.*.humidity.required'                        => 'Please enter the humidity.',
            'form.hatcher_readings.*.humidity.numeric'                         => 'Humidity must be a number.',
            'form.random_humidity_entrance_dumper.required'                    => 'Please enter the humidity reading.',
            'form.random_humidity_top_baggy.required'                          => 'Please enter the humidity reading.',
            'form.random_entrance_dumper_incubator.required'                   => 'Please enter the reading.',
            'form.aircon_count.required'                                       => 'Please select the number of aircons open.',
            'form.humidity_incubator_hallway.required'                         => 'Please enter the humidity at incubator hallway.',
            'form.humidity_outside.required'                                   => 'Please enter the humidity outside.',
            'form.weather_condition.required'                                  => 'Please select the weather condition.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Plenum Temperature and Humidity Monitoring';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatcheryman'  => '',
            'date'         => '',
            'shift'        => '',
            'time'         => '',

            'incubator_readings' => [
                ['incubator_id' => '', 'temperature' => '', 'humidity' => ''],
            ],
            'hatcher_readings' => [
                ['hatcher_id' => '', 'temperature' => '', 'humidity' => ''],
            ],
            'plenum_photos' => [],

            'random_humidity_entrance_dumper'  => '',
            'random_humidity_top_baggy'        => '',
            'random_entrance_dumper_incubator' => '',

            'aircon_count'              => '',
            'humidity_incubator_hallway' => '',
            'humidity_outside'           => '',
            'weather_condition'          => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatcheryman', 'date', 'shift', 'time'],
            2 => ['incubator_readings', 'hatcher_readings', 'plenum_photos'],
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
