<?php

namespace App\Livewire\Configs;

class PasgarScoreConfig
{
    public static function getRules(): array
    {
        return [
            'form.personnel_name'          => 'required|string|max:255',
            'form.hatch_date'              => 'required|date',
            'form.time_started'            => 'required|string',
            'form.ps_number'               => 'required|integer|exists:ps-numbers,id',
            'form.house_number'            => 'required|integer|exists:house-numbers,id',
            'form.incubator_number'        => 'required|integer|exists:incubator-machines,id',
            'form.hatcher_number'          => 'required|integer|exists:hatcher-machines,id',
            'form.average_chick_weight'    => 'required|numeric|min:0',
            'form.low_reflex_alertness_qty' => 'required|integer|min:0',
            'form.navel_issue_qty'         => 'required|integer|min:0',
            'form.leg_issue_qty'           => 'required|integer|min:0',
            'form.beak_issue_qty'          => 'required|integer|min:0',
            'form.belly_bloated_qty'       => 'required|integer|min:0',
            'form.pasgar_average_scoring'  => 'required|string|max:255',
            'form.dop_prime_qty'           => 'required|integer|min:0',
            'form.dop_prime_box_numbers'   => 'required|string|max:255',
            'form.dop_jr_prime_qty'        => 'required|integer|min:0',
            'form.dop_jr_prime_box_numbers' => 'required|string|max:255',
            'form.qc_personnel'            => 'required|string|max:255',
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
            'form.personnel_name.required'           => 'Please enter the name of the personnel who performed PASGAR scoring.',
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
            'form.average_chick_weight.required'     => 'Please enter the average chick weight.',
            'form.average_chick_weight.numeric'      => 'Please enter a valid number for average chick weight.',
            'form.low_reflex_alertness_qty.required' => 'Please enter the Low Reflex / Alertness quantity.',
            'form.navel_issue_qty.required'          => 'Please enter the Navel Issue quantity.',
            'form.leg_issue_qty.required'            => 'Please enter the Leg Issue quantity.',
            'form.beak_issue_qty.required'           => 'Please enter the Beak Issue quantity.',
            'form.belly_bloated_qty.required'        => 'Please enter the Belly / Bloated quantity.',
            'form.pasgar_average_scoring.required'   => 'Please enter the PASGAR average scoring.',
            'form.dop_prime_qty.required'            => 'Please enter the DOP Prime quantity.',
            'form.dop_prime_box_numbers.required'    => 'Please enter the DOP Prime box numbers.',
            'form.dop_jr_prime_qty.required'         => 'Please enter the DOP JR Prime quantity.',
            'form.dop_jr_prime_box_numbers.required' => 'Please enter the DOP JR Prime box numbers.',
            'form.qc_personnel.required'             => 'Please enter the QC personnel name.',
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
            'personnel_name'           => '',
            'hatch_date'               => '',
            'time_started'             => '',
            'ps_number'                => '',
            'house_number'             => '',
            'incubator_number'         => '',
            'hatcher_number'           => '',
            'average_chick_weight'     => '',
            'low_reflex_alertness_qty' => '',
            'navel_issue_qty'          => '',
            'leg_issue_qty'            => '',
            'beak_issue_qty'           => '',
            'belly_bloated_qty'        => '',
            'pasgar_average_scoring'   => '',
            'dop_prime_qty'            => '',
            'dop_prime_box_numbers'    => '',
            'dop_jr_prime_qty'         => '',
            'dop_jr_prime_box_numbers' => '',
            'qc_personnel'             => '',
            'time_finished'            => '',
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
                'average_chick_weight',
                'low_reflex_alertness_qty',
                'navel_issue_qty',
                'leg_issue_qty',
                'beak_issue_qty',
                'belly_bloated_qty',
                'pasgar_average_scoring',
            ],
            3 => [
                'dop_prime_qty',
                'dop_prime_box_numbers',
                'dop_jr_prime_qty',
                'dop_jr_prime_box_numbers',
                'form_photo',
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
                'low_reflex_alertness_qty',
                'navel_issue_qty',
                'leg_issue_qty',
                'beak_issue_qty',
                'belly_bloated_qty',
                'pasgar_average_scoring',
                'dop_prime_qty',
                'dop_prime_box_numbers',
                'dop_jr_prime_qty',
                'dop_jr_prime_box_numbers',
                'form_photo',
                'qc_personnel',
                'time_finished',
            ],
        ];
    }
}
