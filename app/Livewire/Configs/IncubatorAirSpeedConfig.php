<?php

namespace App\Livewire\Configs;

class IncubatorAirSpeedConfig
{
    public static function getRules(): array
    {
        return [
            'form.incubator' => 'required|integer|exists:incubator-machines,id',
            'form.hatchery_man' => 'required|array|min:1',
            'form.hatchery_man.*' => 'integer|exists:users,id',
            'form.left_baggy_top_reading' => 'required|string',
            'form.left_baggy_middle_reading' => 'required|string',
            'form.left_baggy_bottom_reading' => 'required|string',
            'form.right_baggy_top_reading' => 'required|string',
            'form.right_baggy_middle_reading' => 'required|string',
            'form.right_baggy_bottom_reading' => 'required|string',
            'form.remarks' => 'required|string',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required' => 'Please fill in this field.',
            'integer' => 'Please enter a valid number.',
            'string' => 'Please enter valid text.',
            'max' => 'File size must not exceed 1MB.',
            'image' => 'Please upload a valid image.',
            'form.incubator.required' => 'Please select an incubator.',
            'form.incubator.exists' => 'Please select a valid incubator.',
            'form.hatchery_man.required' => 'Please select at least one hatchery man.',
            'form.hatchery_man.min' => 'Please select at least one hatchery man.',
            'form.left_baggy_top_reading.required' => 'Please enter the left baggy top airspeed reading.',
            'form.left_baggy_middle_reading.required' => 'Please enter the left baggy middle airspeed reading.',
            'form.left_baggy_bottom_reading.required' => 'Please enter the left baggy bottom airspeed reading.',
            'form.right_baggy_top_reading.required' => 'Please enter the right baggy top airspeed reading.',
            'form.right_baggy_middle_reading.required' => 'Please enter the right baggy middle airspeed reading.',
            'form.right_baggy_bottom_reading.required' => 'Please enter the right baggy bottom airspeed reading.',
            'form.remarks.required' => 'Please enter remarks.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Incubator Air Speed Weekly Monitoring';
    }

    public static function defaultFormState(): array
    {
        return [
            'incubator' => '',
            'hatchery_man' => [],
            'left_baggy_top_reading' => '',
            'left_baggy_middle_reading' => '',
            'left_baggy_bottom_reading' => '',
            'right_baggy_top_reading' => '',
            'right_baggy_middle_reading' => '',
            'right_baggy_bottom_reading' => '',
            'remarks' => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['incubator', 'hatchery_man'],
            2 => ['left_baggy_top_reading', 'left_baggy_top_photos'],
            3 => ['left_baggy_middle_reading', 'left_baggy_middle_photos'],
            4 => ['left_baggy_bottom_reading', 'left_baggy_bottom_photos'],
            5 => ['right_baggy_top_reading', 'right_baggy_top_photos'],
            6 => ['right_baggy_middle_reading', 'right_baggy_middle_photos'],
            7 => ['right_baggy_bottom_reading', 'right_baggy_bottom_photos'],
            8 => ['remarks'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => [
                'incubator',
                'hatchery_man',
                'left_baggy_top_reading',
                'left_baggy_top_photos',
                'left_baggy_middle_reading',
                'left_baggy_middle_photos',
                'left_baggy_bottom_reading',
                'left_baggy_bottom_photos',
                'right_baggy_top_reading',
                'right_baggy_top_photos',
                'right_baggy_middle_reading',
                'right_baggy_middle_photos',
                'right_baggy_bottom_reading',
                'right_baggy_bottom_photos',
                'remarks',
            ]
        ];
    }
}
