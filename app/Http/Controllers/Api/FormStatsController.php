<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class FormStatsController extends Controller
{
    /**
     * Get form statistics with dynamic filtering
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'form_type_id' => 'nullable|integer|exists:form_types,id',
            'date_filter' => 'nullable|in:current_month,last_month,current_quarter,last_quarter,last_three_months,current_year,previous_year,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Form::with(['formType']);

        // Apply form type filter
        if ($request->filled('form_type_id')) {
            $query->where('form_type_id', $request->form_type_id);
        }

        // Apply date filtering
        $dateFilter = $request->get('date_filter', 'current_month');
        $startDate = null;
        $endDate = null;

        switch ($dateFilter) {
            case 'current_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'current_quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'last_quarter':
                $startDate = Carbon::now()->subQuarter()->startOfQuarter();
                $endDate = Carbon::now()->subQuarter()->endOfQuarter();
                break;
            case 'last_three_months':
                $startDate = Carbon::now()->subMonths(2)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'current_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'previous_year':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate = Carbon::now()->subYear()->endOfYear();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                } else {
                    return response()->json([
                        'error' => 'Custom date filter requires both start_date and end_date parameters'
                    ], 422);
                }
                break;
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date_submitted', [$startDate, $endDate]);
        }

        // Get all forms without limit
        $forms = $query->orderBy('date_submitted', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_forms' => $forms->count(),
            'date_filter' => $dateFilter,
            'date_range' => [
                'start_date' => $startDate?->format('Y-m-d H:i:s'),
                'end_date' => $endDate?->format('Y-m-d H:i:s'),
            ],
            'forms_by_type' => $forms->groupBy('form_type_id')->map(function ($group) {
                $formType = $group->first()->formType;
                return [
                    'form_type_id' => $formType->id,
                    'form_type_name' => $formType->name,
                    'count' => $group->count(),
                    'forms' => $group->map(function ($form) {
                        return [
                            'id' => $form->id,
                            'form_inputs' => $form->form_inputs,
                            'date_submitted' => $form->date_submitted->format('Y-m-d H:i:s'),
                            'uploaded_by' => $form->uploaded_by,
                        ];
                    })->toArray(),
                ];
            })->values(),
            'all_forms' => $forms->map(function ($form) {
                return [
                    'id' => $form->id,
                    'form_type_id' => $form->form_type_id,
                    'form_type_name' => $form->formType->name,
                    'form_inputs' => $form->form_inputs,
                    'date_submitted' => $form->date_submitted->format('Y-m-d H:i:s'),
                    'uploaded_by' => $form->uploaded_by,
                ];
            })->toArray(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'filters_applied' => [
                'form_type_id' => $request->get('form_type_id'),
                'date_filter' => $dateFilter,
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
            ]
        ]);
    }

    /**
     * Get available form types
     */
    public function formTypes(): JsonResponse
    {
        $formTypes = FormType::all()->map(function ($formType) {
            return [
                'id' => $formType->id,
                'name' => $formType->name,
                'description' => $formType->description ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formTypes
        ]);
    }
}
