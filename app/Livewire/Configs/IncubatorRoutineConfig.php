<?php

namespace App\Livewire\Configs;

class IncubatorRoutineConfig
{
    public static function getRules(): array
    {
        return [
            'shift' => 'required|string|in:1st Shift,2nd Shift,3rd Shift',
            'alarm_system_condition' => 'required|string|in:Operational,Unoperational',
            'corrective_action' => 'required|string|min:3',
            'hatchery_man' => 'required|integer|exists:hatchery_users,id',
            'incubator' => 'required|integer|exists:incubators,id',
            'check_incubator_doors_for_air_leakage' => 'required|string|in:Pending,Done',
            'checking_of_baggy_against_the_gaskets' => 'required|string|in:Pending,Done',
            'check_curtain_position_and_condition' => 'required|string|in:Pending,Done',
            'check_wick_for_replacement_washing' => 'required|string|in:Pending,Done',
            'check_spray_nozzle_and_water_pan' => 'required|string|in:Pending,Done',
            'check_incubator_fans_for_vibration' => 'required|string|in:Pending,Done',
            'check_rack_baffle_condition' => 'required|string|in:Pending,Done',
            'drain_water_out_from_air_compressor_tank' => 'required|string|in:Pending,Done',
            'cleaning_incubator_roof_and_plenum' => 'required|string|in:Pending,Done',
            'check_water_level_of_blue_tank' => 'required|string|in:Pending,Done',
            'cleaning_of_incubator_floor_area' => 'required|string|in:Pending,Done',
            'cleaning_of_entrance_and_exit_area_flooring' => 'required|string|in:Pending,Done',
            'clean_and_refill_water_reservoir' => 'required|string|in:Pending,Done',
            'egg_setting_preparation' => 'required|string|in:Pending,Done',
            'egg_setting' => 'required|string|in:Pending,Done',
            'record_egg_setting_on_board' => 'required|string|in:Pending,Done',
            'record_egg_setting_time' => 'required|string|in:Pending,Done',
            'assist_in_random_candling' => 'required|string|in:Pending,Done',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required' => 'Please fill in this field.',
            'in' => 'Please select a valid option.',
            'integer' => 'Please enter a valid number.',
            'string' => 'Please enter valid text.',
            'min' => 'Please enter a valid value.',
            'max' => 'Please enter a valid value.',
            'shift.required' => 'Please select a shift.',
            'shift.in' => 'Please select a valid shift.',
            'alarm_system_condition.required' => 'Please select the alarm system condition.',
            'alarm_system_condition.in' => 'Please select a valid condition.',
            'corrective_action.required' => 'Please fill in this field.',
            'corrective_action.min' => 'Corrective action must be at least 3 characters.',
            'hatchery_man.required' => 'Please select a hatchery man.',
            'hatchery_man.exists' => 'Please select a valid hatchery man.',
            'incubator.required' => 'Please select an incubator.',
            'incubator.exists' => 'Please select a valid incubator.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Incubator Routine Checklist Per Shift';
    }

    public static function defaultFormState(): array
    {
        return [
            'shift' => '',
            'alarm_system_condition' => '',
            'corrective_action' => '',
            'hatchery_man' => '',
            'incubator' => '',

            'check_incubator_doors_for_air_leakage' => '',
            'checking_of_baggy_against_the_gaskets' => '',
            'check_curtain_position_and_condition' => '',
            'check_wick_for_replacement_washing' => '',
            'check_spray_nozzle_and_water_pan' => '',
            'check_incubator_fans_for_vibration' => '',
            'check_rack_baffle_condition' => '',
            'drain_water_out_from_air_compressor_tank' => '',

            'cleaning_incubator_roof_and_plenum' => '',

            'check_water_level_of_blue_tank' => '',
            'cleaning_of_incubator_floor_area' => '',
            'cleaning_of_entrance_and_exit_area_flooring' => '',
            'clean_and_refill_water_reservoir' => '',

            'egg_setting_preparation' => '',
            'egg_setting' => '',
            'record_egg_setting_on_board' => '',
            'record_egg_setting_time' => '',
            'assist_in_random_candling' => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => ['shift', 'alarm_system_condition', 'corrective_action', 'hatchery_man', 'incubator'],
            2 => [
                'check_incubator_doors_for_air_leakage',
                'checking_of_baggy_against_the_gaskets',
                'check_curtain_position_and_condition',
                'check_wick_for_replacement_washing',
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration',
                'check_rack_baffle_condition',
                'drain_water_out_from_air_compressor_tank',
            ],
            3 => ['cleaning_incubator_roof_and_plenum'],
            4 => [
                'check_water_level_of_blue_tank',
                'cleaning_of_incubator_floor_area',
                'cleaning_of_entrance_and_exit_area_flooring',
                'clean_and_refill_water_reservoir',
            ],
            5 => [
                'egg_setting_preparation',
                'egg_setting',
                'record_egg_setting_on_board',
                'record_egg_setting_time',
                'assist_in_random_candling',
            ],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => [
                'shift', 'alarm_system_condition', 'corrective_action',
                'check_incubator_doors_for_air_leakage',
                'drain_water_out_from_air_compressor_tank',
                'check_water_level_of_blue_tank',
                'hatchery_man',
                'incubator'
            ],
            'Monday-1st Shift' => [
                'cleaning_of_incubator_floor_area',
                'assist_in_random_candling'
            ],
            'Monday-2nd Shift' => [
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration',
                'egg_setting_preparation'
            ],
            'Monday-3rd Shift' => [
                'checking_of_baggy_against_the_gaskets',
                'check_curtain_position_and_condition',
                'check_rack_baffle_condition',
                'egg_setting',
                'record_egg_setting_on_board',
                'record_egg_setting_time'
            ],
            'Tuesday-1st Shift' => [],
            'Tuesday-2nd Shift' => [
                'check_wick_for_replacement_washing',
                'cleaning_of_incubator_floor_area',
                'clean_and_refill_water_reservoir'
            ],
            'Tuesday-3rd Shift' => [
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration'
            ],
            'Wednesday-1st Shift' => [
                'cleaning_of_incubator_floor_area',
                'cleaning_of_entrance_and_exit_area_flooring'
            ],
            'Wednesday-2nd Shift' => [],
            'Wednesday-3rd Shift' => [
                'cleaning_incubator_roof_and_plenum',
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration'
            ],
            'Thursday-1st Shift' => [
                'cleaning_of_incubator_floor_area'
            ],
            'Thursday-2nd Shift' => [
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration',
                'egg_setting_preparation'
            ],
            'Thursday-3rd Shift' => [
                'checking_of_baggy_against_the_gaskets',
                'check_curtain_position_and_condition',
                'check_rack_baffle_condition',
                'egg_setting',
                'record_egg_setting_on_board',
                'record_egg_setting_time'
            ],
            'Friday-1st Shift' => [],
            'Friday-2nd Shift' => [
                'check_wick_for_replacement_washing',
                'cleaning_of_incubator_floor_area',
                'clean_and_refill_water_reservoir'
            ],
            'Friday-3rd Shift' => [
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration'
            ],
            'Saturday-1st Shift' => [],
            'Saturday-2nd Shift' => [],
            'Saturday-3rd Shift' => [
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration'
            ],
            'Sunday-1st Shift' => [
                'cleaning_of_incubator_floor_area'
            ],
            'Sunday-2nd Shift' => [
                'cleaning_of_incubator_floor_area'
            ],
            'Sunday-3rd Shift' => [
                'cleaning_incubator_roof_and_plenum',
                'check_spray_nozzle_and_water_pan',
                'check_incubator_fans_for_vibration'
            ],
        ];
    }
}
