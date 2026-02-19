<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardStats extends Component
{
    public string $search = '';

    public ?string $onlyType = null;

    public bool $showCharts = true;

    public array $typeOptions = [];
    public array $selectedTypes = [];
    public bool $typesSelectionInitialized = false;

    public array $cards = [];
    public array $charts = [];

    public function mount(?string $onlyType = null, bool $showCharts = true): void
    {
        $this->onlyType = $onlyType;
        $this->showCharts = $showCharts;
        $this->loadData();
    }

    public function refreshStats(): void
    {
        $this->loadData();
        if ($this->showCharts) {
            $this->dispatch('dashboardStatsUpdated', charts: $this->charts);
        }
    }

    public function updatedSelectedTypes(): void
    {
        $this->loadData();
        if ($this->showCharts) {
            $this->dispatch('dashboardStatsUpdated', charts: $this->charts);
        }
    }

    public function selectAllTypes(): void
    {
        $this->selectedTypes = array_map(static fn ($t) => (string) ($t['id'] ?? ''), $this->typeOptions);
        $this->selectedTypes = array_values(array_filter($this->selectedTypes, static fn ($v) => $v !== ''));
        $this->updatedSelectedTypes();
    }

    public function clearSelectedTypes(): void
    {
        $this->selectedTypes = [];
        $this->updatedSelectedTypes();
    }

    protected function loadData(): void
    {
        $formTypes = DB::table('form_types')
            ->orderBy('form_name')
            ->get(['id', 'form_name']);

        $typeNamesById = [];
        $typeOptions = [];
        foreach ($formTypes as $ft) {
            $id = (int) $ft->id;
            $name = (string) $ft->form_name;
            $typeNamesById[$id] = $name;
            $typeOptions[] = ['id' => $id, 'name' => $name];
        }

        $this->typeOptions = $typeOptions;

        if (!$this->typesSelectionInitialized && $this->selectedTypes === []) {
            $this->selectedTypes = array_map('strval', array_keys($typeNamesById));
            $this->typesSelectionInitialized = true;
        }

        $selectedIds = array_values(array_unique(array_map('intval', $this->selectedTypes)));
        $selectedIdSet = array_flip($selectedIds);
        $filteredTypeNamesById = array_intersect_key($typeNamesById, $selectedIdSet);

        $now = now();
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $weekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();
        $monthStart = $now->copy()->startOfMonth()->startOfDay();
        $monthEnd = $now->copy()->endOfMonth()->endOfDay();
        $yearStart = $now->copy()->startOfYear()->startOfDay();
        $yearEnd = $now->copy()->endOfYear()->endOfDay();

        // Charts use filtered types
        $weekCounts = $this->countsByType($filteredTypeNamesById, $weekStart, $weekEnd);
        $monthCounts = $this->countsByType($filteredTypeNamesById, $monthStart, $monthEnd);
        $yearCounts = $this->countsByType($filteredTypeNamesById, $yearStart, $yearEnd);

        // Cards always use all types (not filtered)
        $allWeekCounts = $this->countsByType($typeNamesById, $weekStart, $weekEnd);
        $allMonthCounts = $this->countsByType($typeNamesById, $monthStart, $monthEnd);
        $allYearCounts = $this->countsByType($typeNamesById, $yearStart, $yearEnd);

        $cards = [];
        foreach ($typeNamesById as $typeId => $typeName) {
            if ($this->onlyType !== null && $this->onlyType !== '') {
                if ($this->onlyType !== $typeId && $this->onlyType !== $typeName) {
                    continue;
                }
            }

            $cards[] = [
                'type_id' => $typeId,
                'type_name' => $typeName,
                'week' => (int) ($allWeekCounts[$typeName] ?? 0),
                'month' => (int) ($allMonthCounts[$typeName] ?? 0),
                'year' => (int) ($allYearCounts[$typeName] ?? 0),
            ];
        }

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $cards = array_values(array_filter($cards, function ($c) use ($needle) {
                return str_contains(mb_strtolower((string) ($c['type_name'] ?? '')), $needle);
            }));
        }

        $this->cards = $cards;

        if ($this->showCharts) {
            $daysInMonth = $now->daysInMonth;
            $this->charts = [
                'week' => $this->stackedByDayChart($filteredTypeNamesById, $weekStart, 7),
                'month' => $this->lineByDayChart($filteredTypeNamesById, $monthStart, $daysInMonth),
                'year' => $this->doughnutByTypeChart($yearCounts), // 12 months
            ];
        } else {
            $this->charts = [];
        }
    }

    protected function countsByType(array $typeNamesById, Carbon $start, Carbon $end): array
    {
        if ($typeNamesById === []) {
            return [];
        }

        $rows = DB::table('forms')
            ->select('form_type_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('date_submitted')
            ->whereBetween('date_submitted', [$start, $end])
            ->groupBy('form_type_id')
            ->get();

        $counts = [];
        foreach ($typeNamesById as $id => $name) {
            $counts[$name] = 0;
        }

        foreach ($rows as $row) {
            $typeId = (int) $row->form_type_id;
            $name = $typeNamesById[$typeId] ?? null;
            if (!$name) {
                continue;
            }
            $counts[$name] = (int) $row->total;
        }

        return $counts;
    }

    protected function stackedByDayChart(array $typeNamesById, Carbon $start, int $days): array
    {
        $labels = [];
        for ($i = 0; $i < $days; $i++) {
            $labels[] = $start->copy()->addDays($i)->format('M d');
        }

        $rows = DB::table('forms')
            ->select(DB::raw('DATE(date_submitted) as bucket'), 'form_type_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('date_submitted')
            ->whereBetween('date_submitted', [$start->copy()->startOfDay(), $start->copy()->addDays($days - 1)->endOfDay()])
            ->groupBy(DB::raw('DATE(date_submitted)'), 'form_type_id')
            ->get();

        $counts = [];
        foreach ($typeNamesById as $typeId => $typeName) {
            $counts[$typeName] = array_fill(0, $days, 0);
        }

        foreach ($rows as $row) {
            $bucket = (string) $row->bucket;
            $bucketDate = Carbon::parse($bucket);
            $index = (int) $start->copy()->startOfDay()->diffInDays($bucketDate);
            
            if ($index < 0 || $index >= $days) {
                continue;
            }

            $typeName = $typeNamesById[(int) $row->form_type_id] ?? null;
            if (!$typeName) {
                continue;
            }

            $counts[$typeName][$index] = (int) $row->total;
        }

        $datasets = [];
        $colorIndex = 0;
        foreach ($counts as $typeName => $series) {
            $datasets[] = [
                'label' => $typeName,
                'data' => $series,
                'backgroundColor' => $this->colorForIndex($colorIndex, 0.6),
                'borderColor' => $this->colorForIndex($colorIndex, 1),
                'borderWidth' => 1,
            ];
            $colorIndex++;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function lineByDayChart(array $typeNamesById, Carbon $start, int $days): array
    {
        $labels = [];
        for ($i = 0; $i < $days; $i++) {
            $labels[] = $start->copy()->addDays($i)->format('M d');
        }

        $rows = DB::table('forms')
            ->select(DB::raw('DATE(date_submitted) as bucket'), 'form_type_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('date_submitted')
            ->whereBetween('date_submitted', [$start->copy()->startOfDay(), $start->copy()->addDays($days - 1)->endOfDay()])
            ->groupBy(DB::raw('DATE(date_submitted)'), 'form_type_id')
            ->get();

        $counts = [];
        foreach ($typeNamesById as $typeId => $typeName) {
            $counts[$typeName] = array_fill(0, $days, 0);
        }

        foreach ($rows as $row) {
            $bucket = (string) $row->bucket;
            $bucketDate = Carbon::parse($bucket);
            $index = (int) $start->copy()->startOfDay()->diffInDays($bucketDate);
            
            if ($index < 0 || $index >= $days) {
                continue;
            }

            $typeName = $typeNamesById[(int) $row->form_type_id] ?? null;
            if (!$typeName) {
                continue;
            }

            $counts[$typeName][$index] = (int) $row->total;
        }

        $datasets = [];
        $colorIndex = 0;
        foreach ($counts as $typeName => $series) {
            $datasets[] = [
                'label' => $typeName,
                'data' => $series,
                'borderColor' => $this->colorForIndex($colorIndex, 1),
                'backgroundColor' => $this->colorForIndex($colorIndex, 0.15),
                'tension' => 0.25,
                'fill' => false,
            ];
            $colorIndex++;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function doughnutByTypeChart(array $yearCounts): array
    {
        $labels = array_keys($yearCounts);
        $data = array_values($yearCounts);

        $colors = [];
        foreach ($labels as $i => $label) {
            $colors[] = $this->colorForIndex($i, 0.75);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function colorForIndex(int $i, float $alpha): string
    {
        $palette = [
            [59, 130, 246],
            [16, 185, 129],
            [234, 179, 8],
            [239, 68, 68],
            [168, 85, 247],
            [20, 184, 166],
            [249, 115, 22],
            [99, 102, 241],
        ];

        $rgb = $palette[$i % count($palette)];

        return 'rgba(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',' . $alpha . ')';
    }

    public function render()
    {
        return view('livewire.admin.dashboard-stats');
    }
}
