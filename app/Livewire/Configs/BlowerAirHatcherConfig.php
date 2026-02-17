<?php

namespace App\Livewire\Configs;

class BlowerAirHatcherConfig
{
    public static function getRules(): array
    {
        return [
            'form.hatchery_man' => 'required|integer|exists:users,id',
            'form.hatcher' => 'required|integer|exists:hatcher-machines,id',
            'form.cfm_fan_reading' => 'required|string',
            'form.cfm_fan_action_taken' => 'required|string',
            'form.cfm_fan_photos.*' => 'image|max:1024',
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
            'form.hatcher.required' => 'Please select a hatcher.',
            'form.hatcher.exists' => 'Please select a valid hatcher.',
            'form.hatchery_man.required' => 'Please select a hatchery man.',
            'form.hatchery_man.exists' => 'Please select a valid hatchery man.',
            'form.cfm_fan_reading.required' => 'Please enter CFM fan reading.',
            'form.cfm_fan_action_taken.required' => 'Please enter action taken.',
            'form.cfm_fan_photos.required' => 'Please upload photos.',
            'form.cfm_fan_photos.image' => 'Please upload valid images.',
            'form.cfm_fan_photos.max' => 'Photo size must not exceed 1MB.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Hatcher Blower Air Speed Monitoring';
    }

    public static function defaultFormState(): array
    {
        return [
            'hatchery_man' => '',
            'hatcher' => '',
            'cfm_fan_reading' => '',
            'cfm_fan_action_taken' => '',
            'cfm_fan_photos' => []
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['hatcher', 'hatchery_man'],
            2 => ['cfm_fan_reading', 'cfm_fan_action_taken', 'cfm_fan_photos']
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => [
                'hatcher',
                'hatchery_man',
                'cfm_fan_reading', 
                'cfm_fan_action_taken',
                'cfm_fan_photos'
            ]
        ];
    }
}
