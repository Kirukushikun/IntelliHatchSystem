<?php

namespace App\Livewire\Admin;

use App\Models\FormType;
use Carbon\Carbon;
use Carbon\Constants\UnitValue;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InsightsIndex extends Component
{
    public array $formTypes = [];

    public function mount(): void
    {
        $this->loadFormTypes();
    }

    protected function loadFormTypes(): void
    {
        $now = now();
        $weekStart = $now->copy()->startOfWeek(UnitValue::SUNDAY)->startOfDay();
        $weekEnd   = $now->copy()->endOfWeek(UnitValue::SATURDAY)->endOfDay();
        $monthStart = $now->copy()->startOfMonth()->startOfDay();
        $monthEnd   = $now->copy()->endOfMonth()->endOfDay();

        $formTypes = DB::table('form_types')->orderBy('form_name')->get(['id', 'form_name']);

        $typeIds = $formTypes->pluck('id')->toArray();

        $weekCounts  = $this->countsByTypeId($typeIds, $weekStart, $weekEnd);
        $monthCounts = $this->countsByTypeId($typeIds, $monthStart, $monthEnd);

        $this->formTypes = $formTypes->map(function ($ft) use ($weekCounts, $monthCounts) {
            return [
                'id'          => $ft->id,
                'name'        => $ft->form_name,
                'week_count'  => (int) ($weekCounts[$ft->id] ?? 0),
                'month_count' => (int) ($monthCounts[$ft->id] ?? 0),
            ];
        })->toArray();
    }

    protected function countsByTypeId(array $typeIds, Carbon $start, Carbon $end): array
    {
        if (empty($typeIds)) {
            return [];
        }

        $rows = DB::table('forms')
            ->select('form_type_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('date_submitted')
            ->whereBetween('date_submitted', [$start, $end])
            ->whereIn('form_type_id', $typeIds)
            ->groupBy('form_type_id')
            ->get();

        $counts = array_fill_keys($typeIds, 0);
        foreach ($rows as $row) {
            $counts[$row->form_type_id] = (int) $row->total;
        }

        return $counts;
    }

    public function render()
    {
        return view('livewire.admin.insights-index');
    }
}
