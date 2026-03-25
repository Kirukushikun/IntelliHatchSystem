<?php

namespace App\Livewire\Configs;

class IncubatorRackPmConfig
{
    public static function getRules(): array
    {
        return [
            'form.rack_number'                          => 'required|string|max:255',
            'form.date'                                 => 'required|date',
            'form.time_started'                         => 'required|string',
            'form.maintenance_personnel'                => 'required|integer|exists:users,id',
            'form.chord_connection_status'              => 'required|in:Yes,No',
            'form.chord_connection_corrective_action'   => 'required|string|max:1000',
            'form.air_hose_status'                      => 'required|in:Yes,No',
            'form.air_hose_corrective_action'           => 'required|string|max:1000',
            'form.wheels_status'                        => 'required|in:Yes,No',
            'form.wheels_corrective_action'             => 'required|string|max:1000',
            'form.steel_frame_status'                   => 'required|in:Yes,No',
            'form.steel_frame_corrective_action'        => 'required|string|max:1000',
            'form.bolts_status'                         => 'required|in:Yes,No',
            'form.bolts_corrective_action'              => 'required|string|max:1000',
            'form.turning_sensor_status'                => 'required|in:Yes,No',
            'form.turning_sensor_corrective_action'     => 'required|string|max:1000',
            'form.pneumatic_cylinder_status'            => 'required|in:Yes,No',
            'form.pneumatic_cylinder_corrective_action' => 'required|string|max:1000',
            'form.smooth_turning_status'                => 'required|in:Yes,No',
            'form.smooth_turning_corrective_action'     => 'required|string|max:1000',
            'form.turning_angle_status'                 => 'required|in:Yes,No',
            'form.left_turning_angle'                   => 'required|numeric',
            'form.right_turning_angle'                  => 'required|numeric',
            'form.turning_angle_corrective_action'      => 'required|string|max:1000',
            'form.lubricate_bolts_status'               => 'required|in:Yes,No',
            'form.lubricate_bolts_corrective_action'    => 'required|string|max:1000',
            'form.plastic_curtain_status'               => 'required|in:Yes,No',
            'form.plastic_curtain_corrective_action'    => 'required|string|max:1000',
            'form.time_finished'                        => 'required|string',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required' => 'This field is required.',
            'string'   => 'Please enter valid text.',
            'numeric'  => 'Please enter a valid number.',
            'date'     => 'Please enter a valid date.',
            'in'       => 'Please select Yes or No.',
            'exists'   => 'Please select a valid option.',
            'max'      => 'This field is too long.',
            'form.rack_number.required'                              => 'Please enter the incubator rack number.',
            'form.date.required'                                     => 'Please enter the date.',
            'form.time_started.required'                             => 'Please enter the time started.',
            'form.maintenance_personnel.required'                    => 'Please select the maintenance personnel.',
            'form.maintenance_personnel.exists'                      => 'Please select a valid maintenance personnel.',
            'form.chord_connection_status.required'                  => 'Please indicate the chord connection status.',
            'form.chord_connection_corrective_action.required'       => 'Please enter the corrective action or N/A.',
            'form.air_hose_status.required'                          => 'Please indicate the air hose connection status.',
            'form.air_hose_corrective_action.required'               => 'Please enter the corrective action or N/A.',
            'form.wheels_status.required'                            => 'Please indicate the wheels condition status.',
            'form.wheels_corrective_action.required'                 => 'Please enter the corrective action or N/A.',
            'form.steel_frame_status.required'                       => 'Please indicate the steel frame status.',
            'form.steel_frame_corrective_action.required'            => 'Please enter the corrective action or N/A.',
            'form.bolts_status.required'                             => 'Please indicate the bolts connection status.',
            'form.bolts_corrective_action.required'                  => 'Please enter the corrective action or N/A.',
            'form.turning_sensor_status.required'                    => 'Please indicate the turning sensor status.',
            'form.turning_sensor_corrective_action.required'         => 'Please enter the corrective action or N/A.',
            'form.pneumatic_cylinder_status.required'                => 'Please indicate the pneumatic cylinder status.',
            'form.pneumatic_cylinder_corrective_action.required'     => 'Please enter the corrective action or N/A.',
            'form.smooth_turning_status.required'                    => 'Please indicate the smooth turning status.',
            'form.smooth_turning_corrective_action.required'         => 'Please enter the corrective action or N/A.',
            'form.turning_angle_status.required'                     => 'Please indicate the turning angle status.',
            'form.left_turning_angle.required'                       => 'Please enter the left turning angle reading.',
            'form.left_turning_angle.numeric'                        => 'Please enter a valid number for left turning angle.',
            'form.right_turning_angle.required'                      => 'Please enter the right turning angle reading.',
            'form.right_turning_angle.numeric'                       => 'Please enter a valid number for right turning angle.',
            'form.turning_angle_corrective_action.required'          => 'Please enter the corrective action or N/A.',
            'form.lubricate_bolts_status.required'                   => 'Please indicate the lubricate bolts status.',
            'form.lubricate_bolts_corrective_action.required'        => 'Please enter the corrective action or N/A.',
            'form.plastic_curtain_status.required'                   => 'Please indicate the plastic curtain condition status.',
            'form.plastic_curtain_corrective_action.required'        => 'Please enter the corrective action or N/A.',
            'form.time_finished.required'                            => 'Please enter the time finished.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'Incubator Rack Preventive Maintenance Checklist';
    }

    public static function defaultFormState(): array
    {
        return [
            'rack_number'                          => '',
            'date'                                 => '',
            'time_started'                         => '',
            'maintenance_personnel'                => '',
            'chord_connection_status'              => '',
            'chord_connection_corrective_action'   => '',
            'air_hose_status'                      => '',
            'air_hose_corrective_action'           => '',
            'wheels_status'                        => '',
            'wheels_corrective_action'             => '',
            'steel_frame_status'                   => '',
            'steel_frame_corrective_action'        => '',
            'bolts_status'                         => '',
            'bolts_corrective_action'              => '',
            'turning_sensor_status'                => '',
            'turning_sensor_corrective_action'     => '',
            'pneumatic_cylinder_status'            => '',
            'pneumatic_cylinder_corrective_action' => '',
            'smooth_turning_status'                => '',
            'smooth_turning_corrective_action'     => '',
            'turning_angle_status'                 => '',
            'left_turning_angle'                   => '',
            'right_turning_angle'                  => '',
            'turning_angle_corrective_action'      => '',
            'lubricate_bolts_status'               => '',
            'lubricate_bolts_corrective_action'    => '',
            'plastic_curtain_status'               => '',
            'plastic_curtain_corrective_action'    => '',
            'time_finished'                        => '',
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1  => ['rack_number', 'date', 'time_started', 'maintenance_personnel'],
            2  => ['chord_connection_status', 'chord_connection_corrective_action', 'photo_chord_connection'],
            3  => ['air_hose_status', 'air_hose_corrective_action', 'photo_air_hose'],
            4  => ['wheels_status', 'wheels_corrective_action', 'photo_wheels'],
            5  => ['steel_frame_status', 'steel_frame_corrective_action', 'photo_steel_frame'],
            6  => ['bolts_status', 'bolts_corrective_action', 'photo_bolts'],
            7  => ['turning_sensor_status', 'turning_sensor_corrective_action', 'photo_turning_sensor'],
            8  => ['pneumatic_cylinder_status', 'pneumatic_cylinder_corrective_action', 'photo_pneumatic_cylinder'],
            9  => ['smooth_turning_status', 'smooth_turning_corrective_action', 'photo_smooth_turning'],
            10 => ['turning_angle_status', 'left_turning_angle', 'right_turning_angle', 'turning_angle_corrective_action', 'photo_turning_angle'],
            11 => ['lubricate_bolts_status', 'lubricate_bolts_corrective_action', 'photo_lubricate_bolts'],
            12 => ['plastic_curtain_status', 'plastic_curtain_corrective_action', 'photo_plastic_curtain', 'time_finished'],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => [
                'rack_number',
                'date',
                'time_started',
                'maintenance_personnel',
                'chord_connection_status',
                'chord_connection_corrective_action',
                'photo_chord_connection',
                'air_hose_status',
                'air_hose_corrective_action',
                'photo_air_hose',
                'wheels_status',
                'wheels_corrective_action',
                'photo_wheels',
                'steel_frame_status',
                'steel_frame_corrective_action',
                'photo_steel_frame',
                'bolts_status',
                'bolts_corrective_action',
                'photo_bolts',
                'turning_sensor_status',
                'turning_sensor_corrective_action',
                'photo_turning_sensor',
                'pneumatic_cylinder_status',
                'pneumatic_cylinder_corrective_action',
                'photo_pneumatic_cylinder',
                'smooth_turning_status',
                'smooth_turning_corrective_action',
                'photo_smooth_turning',
                'turning_angle_status',
                'left_turning_angle',
                'right_turning_angle',
                'turning_angle_corrective_action',
                'photo_turning_angle',
                'lubricate_bolts_status',
                'lubricate_bolts_corrective_action',
                'photo_lubricate_bolts',
                'plastic_curtain_status',
                'plastic_curtain_corrective_action',
                'photo_plastic_curtain',
                'time_finished',
            ],
        ];
    }
}
