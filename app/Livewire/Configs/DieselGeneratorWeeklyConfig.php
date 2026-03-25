<?php

namespace App\Livewire\Configs;

class DieselGeneratorWeeklyConfig
{
    public static function getRules(): array
    {
        return [
            'form.technician_id'                        => 'required|integer|exists:users,id',
            'form.gen_set_number'                       => 'required|integer|exists:get-sets,id',

            // LUBRICATION
            'form.lub_leaks_status'                     => 'required|in:Okay,Not Okay',
            'form.lub_leaks_problem'                    => 'required|string|max:1000',
            'form.lub_leaks_corrective_action'          => 'required|string|max:1000',
            'form.lub_oil_level_status'                 => 'required|in:Okay,Not Okay',
            'form.lub_oil_level_problem'                => 'required|string|max:1000',
            'form.lub_oil_level_corrective_action'      => 'required|string|max:1000',

            // COOLING SYSTEM
            'form.cool_leaks_status'                    => 'required|in:Okay,Not Okay',
            'form.cool_leaks_problem'                   => 'required|string|max:1000',
            'form.cool_leaks_corrective_action'         => 'required|string|max:1000',
            'form.cool_radiator_status'                 => 'required|in:Okay,Not Okay',
            'form.cool_radiator_problem'                => 'required|string|max:1000',
            'form.cool_radiator_corrective_action'      => 'required|string|max:1000',
            'form.cool_hose_status'                     => 'required|in:Okay,Not Okay',
            'form.cool_hose_problem'                    => 'required|string|max:1000',
            'form.cool_hose_corrective_action'          => 'required|string|max:1000',
            'form.cool_coolant_level_status'            => 'required|in:Okay,Not Okay',
            'form.cool_coolant_level_problem'           => 'required|string|max:1000',
            'form.cool_coolant_level_corrective_action' => 'required|string|max:1000',
            'form.cool_belt_status'                     => 'required|in:Okay,Not Okay',
            'form.cool_belt_problem'                    => 'required|string|max:1000',
            'form.cool_belt_corrective_action'          => 'required|string|max:1000',

            // FUEL
            'form.fuel_leaks_status'                    => 'required|in:Okay,Not Okay',
            'form.fuel_leaks_problem'                   => 'required|string|max:1000',
            'form.fuel_leaks_corrective_action'         => 'required|string|max:1000',

            // AIR IN-TAKE
            'form.air_intake_leaks_status'              => 'required|in:Okay,Not Okay',
            'form.air_intake_leaks_problem'             => 'required|string|max:1000',
            'form.air_intake_leaks_corrective_action'   => 'required|string|max:1000',
            'form.air_intake_cleaner_status'            => 'required|in:Okay,Not Okay',
            'form.air_intake_cleaner_problem'           => 'required|string|max:1000',
            'form.air_intake_cleaner_corrective_action' => 'required|string|max:1000',

            // EXHAUST
            'form.exhaust_leaks_status'                 => 'required|in:Okay,Not Okay',
            'form.exhaust_leaks_problem'                => 'required|string|max:1000',
            'form.exhaust_leaks_corrective_action'      => 'required|string|max:1000',

            // ENGINE RELATED
            'form.engine_vibration_status'              => 'required|in:Okay,Not Okay',
            'form.engine_vibration_problem'             => 'required|string|max:1000',
            'form.engine_vibration_corrective_action'   => 'required|string|max:1000',

            // MAIN GENERATOR
            'form.main_gen_air_status'                  => 'required|in:Okay,Not Okay',
            'form.main_gen_air_problem'                 => 'required|string|max:1000',
            'form.main_gen_air_corrective_action'       => 'required|string|max:1000',
            'form.main_gen_windings_status'             => 'required|in:Okay,Not Okay',
            'form.main_gen_windings_problem'            => 'required|string|max:1000',
            'form.main_gen_windings_corrective_action'  => 'required|string|max:1000',

            // SWITCH GEAR
            'form.switch_gear_status'                   => 'required|in:Okay,Not Okay',
            'form.switch_gear_problem'                  => 'required|string|max:1000',
            'form.switch_gear_corrective_action'        => 'required|string|max:1000',

            // TEST RUN
            'form.test_run_conducted'                   => 'required|in:Conducted,Not Conducted',
            'form.test_run_time'                        => 'required|string|max:500',
            'form.previous_running_time'                => 'required|string|max:255',
            'form.present_running_time'                 => 'required|string|max:255',
            'form.line_voltages'                        => 'required|string|max:500',
            'form.line_amperes'                         => 'required|string|max:500',
            'form.hertz_reading'                        => 'required|string|max:255',
            'form.oil_pressure_kpa'                     => 'required|string|max:255',
            'form.oil_temperature_f'                    => 'required|string|max:255',
            'form.running_condition'                    => 'required|in:Normal,Abnormal',

            // SUMMARY
            'form.notes'                                => 'required|string|max:2000',
            'form.diesel_tank_level'                    => 'required|in:Full Tank,Half Tank,For Refill',
            'form.refill_date'                          => 'required|string|max:500',
            'form.available_diesel_stock'               => 'required|string|max:255',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required' => 'This field is required.',
            'string'   => 'Please enter valid text.',
            'date'     => 'Please enter a valid date.',
            'email'    => 'Please enter a valid email address.',
            'in'       => 'Please select a valid option.',
            'exists'   => 'Please select a valid option.',
            'max'      => 'This field is too long.',

            'form.technician_id.required'                        => 'Please select the maintenance technician.',
            'form.technician_id.exists'                          => 'Please select a valid maintenance technician.',
            'form.gen_set_number.required'                       => 'Please select the diesel generator set.',
            'form.gen_set_number.exists'                         => 'Please select a valid diesel generator set.',

            'form.lub_leaks_status.required'                     => 'Please indicate the lubrication leaks status.',
            'form.lub_leaks_problem.required'                    => 'Please describe the problem or write "N/A".',
            'form.lub_leaks_corrective_action.required'          => 'Please enter the corrective action or write "N/A".',
            'form.lub_oil_level_status.required'                 => 'Please indicate the oil level status.',
            'form.lub_oil_level_problem.required'                => 'Please describe the problem or write "N/A".',
            'form.lub_oil_level_corrective_action.required'      => 'Please enter the corrective action or write "N/A".',

            'form.cool_leaks_status.required'                    => 'Please indicate the cooling system leaks status.',
            'form.cool_leaks_problem.required'                   => 'Please describe the problem or write "N/A".',
            'form.cool_leaks_corrective_action.required'         => 'Please enter the corrective action or write "N/A".',
            'form.cool_radiator_status.required'                 => 'Please indicate the radiator restriction status.',
            'form.cool_radiator_problem.required'                => 'Please describe the problem or write "N/A".',
            'form.cool_radiator_corrective_action.required'      => 'Please enter the corrective action or write "N/A".',
            'form.cool_hose_status.required'                     => 'Please indicate the hose and connections status.',
            'form.cool_hose_problem.required'                    => 'Please describe the problem or write "N/A".',
            'form.cool_hose_corrective_action.required'          => 'Please enter the corrective action or write "N/A".',
            'form.cool_coolant_level_status.required'            => 'Please indicate the coolant level status.',
            'form.cool_coolant_level_problem.required'           => 'Please describe the problem or write "N/A".',
            'form.cool_coolant_level_corrective_action.required' => 'Please enter the corrective action or write "N/A".',
            'form.cool_belt_status.required'                     => 'Please indicate the belt condition and tension status.',
            'form.cool_belt_problem.required'                    => 'Please describe the problem or write "N/A".',
            'form.cool_belt_corrective_action.required'          => 'Please enter the corrective action or write "N/A".',

            'form.fuel_leaks_status.required'                    => 'Please indicate the fuel leaks status.',
            'form.fuel_leaks_problem.required'                   => 'Please describe the problem or write "N/A".',
            'form.fuel_leaks_corrective_action.required'         => 'Please enter the corrective action or write "N/A".',

            'form.air_intake_leaks_status.required'              => 'Please indicate the air in-take leaks status.',
            'form.air_intake_leaks_problem.required'             => 'Please describe the problem or write "N/A".',
            'form.air_intake_leaks_corrective_action.required'   => 'Please enter the corrective action or write "N/A".',
            'form.air_intake_cleaner_status.required'            => 'Please indicate the air cleaner restriction status.',
            'form.air_intake_cleaner_problem.required'           => 'Please describe the problem or write "N/A".',
            'form.air_intake_cleaner_corrective_action.required' => 'Please enter the corrective action or write "N/A".',

            'form.exhaust_leaks_status.required'                 => 'Please indicate the exhaust leaks status.',
            'form.exhaust_leaks_problem.required'                => 'Please describe the problem or write "N/A".',
            'form.exhaust_leaks_corrective_action.required'      => 'Please enter the corrective action or write "N/A".',

            'form.engine_vibration_status.required'              => 'Please indicate the engine vibration status.',
            'form.engine_vibration_problem.required'             => 'Please describe the problem or write "N/A".',
            'form.engine_vibration_corrective_action.required'   => 'Please enter the corrective action or write "N/A".',

            'form.main_gen_air_status.required'                  => 'Please indicate the main generator air inlet/outlet status.',
            'form.main_gen_air_problem.required'                 => 'Please describe the problem or write "N/A".',
            'form.main_gen_air_corrective_action.required'       => 'Please enter the corrective action or write "N/A".',
            'form.main_gen_windings_status.required'             => 'Please indicate the windings and electrical connections status.',
            'form.main_gen_windings_problem.required'            => 'Please describe the problem or write "N/A".',
            'form.main_gen_windings_corrective_action.required'  => 'Please enter the corrective action or write "N/A".',

            'form.switch_gear_status.required'                   => 'Please indicate the switch gear status.',
            'form.switch_gear_problem.required'                  => 'Please describe the problem or write "N/A".',
            'form.switch_gear_corrective_action.required'        => 'Please enter the corrective action or write "N/A".',

            'form.test_run_conducted.required'                   => 'Please indicate if the test run was conducted.',
            'form.test_run_time.required'                        => 'Please enter the test run time or write "N/A".',
            'form.previous_running_time.required'                => 'Please enter the previous running time reading.',
            'form.present_running_time.required'                 => 'Please enter the present running time reading.',
            'form.line_voltages.required'                        => 'Please enter the three line voltages reading.',
            'form.line_amperes.required'                         => 'Please enter the three line amperes reading.',
            'form.hertz_reading.required'                        => 'Please enter the hertz reading.',
            'form.oil_pressure_kpa.required'                     => 'Please enter the oil pressure reading.',
            'form.oil_temperature_f.required'                    => 'Please enter the oil temperature reading.',
            'form.running_condition.required'                    => 'Please indicate the running condition.',

            'form.notes.required'                                => 'Please enter notes or write "N/A".',
            'form.diesel_tank_level.required'                    => 'Please indicate the diesel tank level.',
            'form.refill_date.required'                          => 'Please enter the refill date or write "N/A".',
            'form.available_diesel_stock.required'               => 'Please enter the available diesel stock level.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Hatchery Diesel Generator Weekly Maintenance Checklist';
    }

    public static function defaultFormState(): array
    {
        return [
            'technician_id'                        => '',
            'gen_set_number'                       => '',

            // LUBRICATION
            'lub_leaks_status'                     => '',
            'lub_leaks_problem'                    => '',
            'lub_leaks_corrective_action'          => '',
            'lub_oil_level_status'                 => '',
            'lub_oil_level_problem'                => '',
            'lub_oil_level_corrective_action'      => '',

            // COOLING SYSTEM
            'cool_leaks_status'                    => '',
            'cool_leaks_problem'                   => '',
            'cool_leaks_corrective_action'         => '',
            'cool_radiator_status'                 => '',
            'cool_radiator_problem'                => '',
            'cool_radiator_corrective_action'      => '',
            'cool_hose_status'                     => '',
            'cool_hose_problem'                    => '',
            'cool_hose_corrective_action'          => '',
            'cool_coolant_level_status'            => '',
            'cool_coolant_level_problem'           => '',
            'cool_coolant_level_corrective_action' => '',
            'cool_belt_status'                     => '',
            'cool_belt_problem'                    => '',
            'cool_belt_corrective_action'          => '',

            // FUEL
            'fuel_leaks_status'                    => '',
            'fuel_leaks_problem'                   => '',
            'fuel_leaks_corrective_action'         => '',

            // AIR IN-TAKE
            'air_intake_leaks_status'              => '',
            'air_intake_leaks_problem'             => '',
            'air_intake_leaks_corrective_action'   => '',
            'air_intake_cleaner_status'            => '',
            'air_intake_cleaner_problem'           => '',
            'air_intake_cleaner_corrective_action' => '',

            // EXHAUST
            'exhaust_leaks_status'                 => '',
            'exhaust_leaks_problem'                => '',
            'exhaust_leaks_corrective_action'      => '',

            // ENGINE RELATED
            'engine_vibration_status'              => '',
            'engine_vibration_problem'             => '',
            'engine_vibration_corrective_action'   => '',

            // MAIN GENERATOR
            'main_gen_air_status'                  => '',
            'main_gen_air_problem'                 => '',
            'main_gen_air_corrective_action'       => '',
            'main_gen_windings_status'             => '',
            'main_gen_windings_problem'            => '',
            'main_gen_windings_corrective_action'  => '',

            // SWITCH GEAR
            'switch_gear_status'                   => '',
            'switch_gear_problem'                  => '',
            'switch_gear_corrective_action'        => '',

            // TEST RUN
            'test_run_conducted'                   => '',
            'test_run_time'                        => '',
            'previous_running_time'                => '',
            'present_running_time'                 => '',
            'line_voltages'                        => '',
            'line_amperes'                         => '',
            'hertz_reading'                        => '',
            'oil_pressure_kpa'                     => '',
            'oil_temperature_f'                    => '',
            'running_condition'                    => '',

            // SUMMARY
            'notes'                                => '',
            'diesel_tank_level'                    => '',
            'refill_date'                          => '',
            'available_diesel_stock'               => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1  => ['technician_id', 'gen_set_number'],
            2  => ['lub_leaks_status', 'lub_leaks_problem', 'lub_leaks_corrective_action', 'photo_lub_leaks'],
            3  => ['lub_oil_level_status', 'lub_oil_level_problem', 'lub_oil_level_corrective_action', 'photo_lub_oil_level'],
            4  => ['cool_leaks_status', 'cool_leaks_problem', 'cool_leaks_corrective_action', 'photo_cool_leaks'],
            5  => ['cool_radiator_status', 'cool_radiator_problem', 'cool_radiator_corrective_action', 'photo_cool_radiator'],
            6  => ['cool_hose_status', 'cool_hose_problem', 'cool_hose_corrective_action', 'photo_cool_hose'],
            7  => ['cool_coolant_level_status', 'cool_coolant_level_problem', 'cool_coolant_level_corrective_action', 'photo_cool_coolant_level'],
            8  => ['cool_belt_status', 'cool_belt_problem', 'cool_belt_corrective_action', 'photo_cool_belt'],
            9  => ['fuel_leaks_status', 'fuel_leaks_problem', 'fuel_leaks_corrective_action', 'photo_fuel_leaks'],
            10 => ['air_intake_leaks_status', 'air_intake_leaks_problem', 'air_intake_leaks_corrective_action', 'photo_air_intake_leaks'],
            11 => ['air_intake_cleaner_status', 'air_intake_cleaner_problem', 'air_intake_cleaner_corrective_action', 'photo_air_intake_cleaner'],
            12 => ['exhaust_leaks_status', 'exhaust_leaks_problem', 'exhaust_leaks_corrective_action', 'photo_exhaust_leaks'],
            13 => ['engine_vibration_status', 'engine_vibration_problem', 'engine_vibration_corrective_action', 'photo_engine_vibration'],
            14 => ['main_gen_air_status', 'main_gen_air_problem', 'main_gen_air_corrective_action', 'photo_main_gen_air'],
            15 => ['main_gen_windings_status', 'main_gen_windings_problem', 'main_gen_windings_corrective_action', 'photo_main_gen_windings'],
            16 => ['switch_gear_status', 'switch_gear_problem', 'switch_gear_corrective_action', 'photo_switch_gear'],
            17 => ['test_run_conducted', 'test_run_time', 'previous_running_time', 'present_running_time', 'line_voltages', 'line_amperes', 'hertz_reading', 'oil_pressure_kpa', 'oil_temperature_f', 'running_condition'],
            18 => ['notes', 'diesel_tank_level', 'refill_date', 'available_diesel_stock'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => array_keys(self::defaultFormState()),
        ];
    }
}
