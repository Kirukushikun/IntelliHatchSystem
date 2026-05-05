<?php

namespace App\Livewire\Configs;

class PasgarScoreConfig
{
    public static function getRules(): array
    {
        return [
            'form.personnel_name'          => 'required|array|min:1',
            'form.personnel_name.*'        => 'integer|exists:users,id',
            'form.hatch_date'              => 'required|date',
            'form.time_started'            => 'required|string',
            'form.ps_number'               => 'required|integer|exists:ps-numbers,id',
            'form.house_number'            => 'required|integer|exists:house-numbers,id',
            'form.incubator_number'        => 'required|integer|exists:incubator-machines,id',
            'form.hatcher_number'          => 'required|integer|exists:hatcher-machines,id',
            'form.samples'                 => 'required|array|min:1',
            'form.samples.*.chick_weight'  => 'required|numeric|min:0',
            'form.samples.*.low_reflex_alertness' => 'boolean',
            'form.samples.*.navel_issue'   => 'boolean',
            'form.samples.*.leg_issue'     => 'boolean',
            'form.samples.*.beak_issue'    => 'boolean',
            'form.samples.*.belly_bloated'    => 'boolean',
            'form.samples.*.vaccination_issue' => 'boolean',
            'form.qc_personnel'            => 'required|array|min:1',
            'form.qc_personnel.*'          => 'integer|exists:users,id',
            'form.time_finished'           => 'required|string',
        ];
    }

    public static function getMessages(): array
    {
        return [
            'required' => 'Please fill in this field.',
            'string'   => 'Please enter valid text.',
            'integer'  => 'Please enter a valid whole number.',
            'numeric'  => 'Please enter a valid number.',
            'min'      => 'Value must be 0 or greater.',
            'date'     => 'Please enter a valid date.',
            'exists'   => 'Please select a valid option.',
            'max'      => 'This field is too long.',
            'form.personnel_name.required'           => 'Please select at least one personnel who performed PASGAR scoring.',
            'form.personnel_name.min'                => 'Please select at least one personnel who performed PASGAR scoring.',
            'form.hatch_date.required'               => 'Please enter the hatch date.',
            'form.time_started.required'             => 'Please enter the time started.',
            'form.ps_number.required'                => 'Please select a PS number.',
            'form.ps_number.exists'                  => 'Please select a valid PS number.',
            'form.house_number.required'             => 'Please select a house number.',
            'form.house_number.exists'               => 'Please select a valid house number.',
            'form.incubator_number.required'         => 'Please select an incubator.',
            'form.incubator_number.exists'           => 'Please select a valid incubator.',
            'form.hatcher_number.required'           => 'Please select a hatcher.',
            'form.hatcher_number.exists'             => 'Please select a valid hatcher.',
            'form.samples.required'                  => 'Please add at least one DOP sample.',
            'form.samples.min'                       => 'Please add at least one DOP sample.',
            'form.samples.*.chick_weight.required'   => 'Please enter the chick weight.',
            'form.samples.*.chick_weight.numeric'    => 'Please enter a valid number for chick weight.',
            'form.qc_personnel.required'             => 'Please select at least one QC personnel.',
            'form.qc_personnel.min'                  => 'Please select at least one QC personnel.',
            'form.time_finished.required'            => 'Please enter the time finished.',
        ];
    }

    public static function getFormTypeName(): string
    {
        return 'PASGAR Score';
    }

    public static function defaultFormState(): array
    {
        return [
            'personnel_name'           => [],
            'hatch_date'               => '',
            'time_started'             => '',
            'ps_number'                => '',
            'house_number'             => '',
            'incubator_number'         => '',
            'hatcher_number'           => '',
            'average_chick_weight'     => '',
            'samples'                  => [],
            'low_reflex_alertness_qty' => 0,
            'navel_issue_qty'          => 0,
            'leg_issue_qty'            => 0,
            'beak_issue_qty'           => 0,
            'belly_bloated_qty'        => 0,
            'pasgar_average_scoring'   => '',
            'qc_personnel'             => [],
            'time_finished'            => '',
        ];
    }

    public static function defaultSample(): array
    {
        return [
            'chick_weight'         => '',
            'low_reflex_alertness' => false,
            'navel_issue'          => false,
            'leg_issue'            => false,
            'beak_issue'           => false,
            'belly_bloated'        => false,
            'vaccination_issue'    => false,
        ];
    }

    public static function stepFieldMap(): array
    {
        return [
            1 => [
                'personnel_name',
                'hatch_date',
                'time_started',
                'ps_number',
                'house_number',
                'incubator_number',
                'hatcher_number',
            ],
            2 => [
                'samples',
            ],
            3 => [
                'qc_personnel',
                'time_finished',
            ],
        ];
    }

    public static function schedule(): array
    {
        return [
            '_daily' => [
                'personnel_name',
                'hatch_date',
                'time_started',
                'ps_number',
                'house_number',
                'incubator_number',
                'hatcher_number',
                'average_chick_weight',
                'samples',
                'qc_personnel',
                'time_finished',
            ],
        ];
    }
}
