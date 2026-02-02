<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FormStatsController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'form_type_id' => 'nullable|integer|exists:form_types,id',
            'date_filter' => 'nullable|in:current_month,last_month,current_quarter,last_quarter,last_three_months,current_year,previous_year,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Form::query();

        // Filter by form type if specified
        if ($request->filled('form_type_id')) {
            $query->where('form_type_id', $request->form_type_id);
        }

        // Apply date filter
        $dateFilter = $request->input('date_filter', 'current_month');
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
                        'error' => 'Custom date filter requires start_date and end_date parameters'
                    ], 422);
                }
                break;
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date_submitted', [$startDate, $endDate]);
        }

        // Get the stats
        $totalForms = $query->count();

        // Get forms by type (if not filtered by specific type)
        $formsByType = [];
        if (!$request->filled('form_type_id')) {
            $formsByType = Form::selectRaw('form_types.name, COUNT(*) as count')
                ->join('form_types', 'forms.form_type_id', '=', 'form_types.id')
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('forms.date_submitted', [$startDate, $endDate]);
                })
                ->groupBy('form_types.id', 'form_types.name')
                ->orderBy('count', 'desc')
                ->get();
        }

        // Get daily submissions for the period
        $dailySubmissions = Form::selectRaw('DATE(date_submitted) as date, COUNT(*) as count')
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('date_submitted', [$startDate, $endDate]);
            })
            ->when($request->filled('form_type_id'), function ($q) use ($request) {
                return $q->where('form_type_id', $request->form_type_id);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'filters' => [
                'form_type_id' => $request->input('form_type_id'),
                'date_filter' => $dateFilter,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
            ],
            'stats' => [
                'total_forms' => $totalForms,
                'period_start' => $startDate?->toDateString(),
                'period_end' => $endDate?->toDateString(),
            ],
            'forms_by_type' => $formsByType,
            'daily_submissions' => $dailySubmissions,
        ]);
    }

    public function quickStats(): JsonResponse
    {
        $now = Carbon::now();
        
        return response()->json([
            'current_month' => Form::whereMonth('date_submitted', $now->month)
                ->whereYear('date_submitted', $now->year)
                ->count(),
            'last_month' => Form::whereMonth('date_submitted', $now->subMonth()->month)
                ->whereYear('date_submitted', $now->subMonth()->year)
                ->count(),
            'current_quarter' => Form::whereBetween('date_submitted', [
                    $now->startOfQuarter(),
                    $now->endOfQuarter()
                ])->count(),
            'last_three_months' => Form::whereBetween('date_submitted', [
                    $now->subMonths(2)->startOfMonth(),
                    $now->endOfMonth()
                ])->count(),
            'current_year' => Form::whereYear('date_submitted', $now->year)->count(),
            'total_users' => User::count(),
            'active_users' => User::where('user_type', 1)->count(),
        ]);
    }
}
