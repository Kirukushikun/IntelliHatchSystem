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
     * Get form statistics with dynamic date filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'form_type_id' => 'nullable|exists:form_types,id',
            'date_filter' => 'required|in:current_month,last_month,current_quarter,last_quarter,last_three_months,current_year,previous_year,custom',
            'start_date' => 'required_if:date_filter,custom|date',
            'end_date' => 'required_if:date_filter,custom|date|after_or_equal:start_date',
        ]);

        $query = Form::query();

        // Filter by form type if specified
        if ($request->filled('form_type_id')) {
            $query->where('form_type_id', $request->form_type_id);
        }

        // Apply date filter
        $dateRange = $this->getDateRange($request->date_filter, $request);
        $query->whereBetween('date_submitted', [$dateRange['start'], $dateRange['end']]);

        // Get statistics
        $stats = $query->selectRaw('
                COUNT(*) as total,
                DATE(date_submitted) as date,
                form_type_id
            ')
            ->groupBy('date', 'form_type_id')
            ->orderBy('date')
            ->get();

        // Get form type information
        $formTypes = FormType::when($request->filled('form_type_id'), function ($q) use ($request) {
                return $q->where('id', $request->form_type_id);
            })
            ->get()
            ->keyBy('id');

        // Format response
        $response = [
            'date_filter' => $request->date_filter,
            'date_range' => [
                'start' => $dateRange['start']->toDateString(),
                'end' => $dateRange['end']->toDateString(),
            ],
            'form_types' => $formTypes->toArray(),
            'stats' => $this->formatStats($stats, $formTypes),
            'summary' => [
                'total_submissions' => $stats->sum('total'),
                'unique_dates' => $stats->count(),
                'form_types_count' => $formTypes->count(),
            ],
        ];

        return response()->json($response);
    }

    /**
     * Get date range based on filter type.
     */
    private function getDateRange(string $filter, Request $request): array
    {
        $now = Carbon::now();

        return match ($filter) {
            'current_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
            ],
            'current_quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
            ],
            'last_quarter' => [
                'start' => $now->copy()->subQuarter()->startOfQuarter(),
                'end' => $now->copy()->subQuarter()->endOfQuarter(),
            ],
            'last_three_months' => [
                'start' => $now->copy()->subMonths(2)->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'current_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'previous_year' => [
                'start' => $now->copy()->subYear()->startOfYear(),
                'end' => $now->copy()->subYear()->endOfYear(),
            ],
            'custom' => [
                'start' => Carbon::parse($request->start_date)->startOfDay(),
                'end' => Carbon::parse($request->end_date)->endOfDay(),
            ],
            default => throw new \InvalidArgumentException("Invalid date filter: {$filter}"),
        };
    }

    /**
     * Format statistics for response.
     */
    private function formatStats($stats, $formTypes): array
    {
        $formatted = [];
        
        foreach ($stats as $stat) {
            $date = $stat->date;
            
            if (!isset($formatted[$date])) {
                $formatted[$date] = [
                    'date' => $date,
                    'total' => 0,
                    'form_types' => [],
                ];
            }
            
            $formatted[$date]['total'] += $stat->total;
            
            if (isset($formTypes[$stat->form_type_id])) {
                $formatted[$date]['form_types'][] = [
                    'id' => $stat->form_type_id,
                    'name' => $formTypes[$stat->form_type_id]->name,
                    'count' => $stat->total,
                ];
            }
        }

        return array_values($formatted);
    }

    /**
     * Get available form types.
     */
    public function formTypes(): JsonResponse
    {
        $formTypes = FormType::select('id', 'name')->orderBy('name')->get();
        
        return response()->json([
            'form_types' => $formTypes,
        ]);
    }

    /**
     * Get quick stats for dashboard.
     */
    public function quickStats(Request $request): JsonResponse
    {
        $request->validate([
            'form_type_id' => 'nullable|exists:form_types,id',
        ]);

        $query = Form::query();

        if ($request->filled('form_type_id')) {
            $query->where('form_type_id', $request->form_type_id);
        }

        $now = Carbon::now();

        $stats = [
            'week' => $query->whereBetween('date_submitted', [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ])->count(),
            'month' => $query->whereBetween('date_submitted', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])->count(),
            'year' => $query->whereBetween('date_submitted', [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
            ])->count(),
        ];

        return response()->json($stats);
    }
}
