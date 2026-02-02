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

    protected function loadData(): void
    {
        $formTypes = DB::table('form_types')
            ->orderBy('form_name')
            ->get(['id', 'form_name']);

        $typeNamesById = [];
        foreach ($formTypes as $ft) {
            $typeNamesById[(int) $ft->id] = (string) $ft->form_name;
        }

        $now = now();
        $weekStart = $now->copy()->subDays(6)->startOfDay();
        $weekEnd = $now->copy()->endOfDay();
        $monthStart = $now->copy()->subDays(29)->startOfDay();
        $monthEnd = $now->copy()->endOfDay();
        $yearStart = $now->copy()->subMonths(11)->startOfMonth();
        $yearEnd = $now->copy()->endOfDay();

        $weekCounts = $this->countsByType($typeNamesById, $weekStart, $weekEnd);
        $monthCounts = $this->countsByType($typeNamesById, $monthStart, $monthEnd);
        $yearCounts = $this->countsByType($typeNamesById, $yearStart, $yearEnd);

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
                'week' => (int) ($weekCounts[$typeName] ?? 0),
                'month' => (int) ($monthCounts[$typeName] ?? 0),
                'year' => (int) ($yearCounts[$typeName] ?? 0),
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
            $this->charts = [
                'week' => $this->stackedByDayChart($typeNamesById, $weekStart, 7),
                'month' => $this->lineByDayChart($typeNamesById, $monthStart, 30),
                'year' => $this->doughnutByTypeChart($yearCounts),
            ];
        } else {
            $this->charts = [];
        }
    }

    protected function countsByType(array $typeNamesById, Carbon $start, Carbon $end): array
    {
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
            $index = (int) Carbon::parse($bucket)->diffInDays($start->copy()->startOfDay());
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
            $index = (int) Carbon::parse($bucket)->diffInDays($start->copy()->startOfDay());
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
