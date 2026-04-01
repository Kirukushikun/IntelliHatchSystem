<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserActivityDashboard extends Component
{
    public string $period = '30'; // days

    public array $summary = [];
    public array $mostActiveUsers = [];
    public array $inactiveUsers = [];
    public array $activityByDayChart = [];
    public array $roleDistributionChart = [];
    public array $topActionsChart = [];

    protected $queryString = [
        'period' => ['except' => '30'],
    ];

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedPeriod(): void
    {
        $this->loadData();
        $this->dispatch('userActivityChartsUpdated', charts: [
            'activityByDay' => $this->activityByDayChart,
            'roleDistribution' => $this->roleDistributionChart,
            'topActions' => $this->topActionsChart,
        ]);
    }

    protected function loadData(): void
    {
        $days = (int) $this->period;
        $since = now()->subDays($days)->startOfDay();

        $this->loadSummary($since);
        $this->loadMostActiveUsers($since);
        $this->loadInactiveUsers($since);
        $this->loadActivityByDayChart($since, $days);
        $this->loadRoleDistributionChart();
        $this->loadTopActionsChart($since);
    }

    protected function loadSummary(Carbon $since): void
    {
        $totalUsers = DB::table('users')->count();
        $disabledUsers = DB::table('users')->where('is_disabled', true)->count();
        $enabledUsers = $totalUsers - $disabledUsers;

        $activeUserIds = DB::table('activity_logs')
            ->where('created_at', '>=', $since)
            ->distinct('user_id')
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->count();

        $inactiveCount = $enabledUsers - $activeUserIds;
        if ($inactiveCount < 0) {
            $inactiveCount = 0;
        }

        $activePercentage = $enabledUsers > 0
            ? round(($activeUserIds / $enabledUsers) * 100, 1)
            : 0;

        $totalActions = DB::table('activity_logs')
            ->where('created_at', '>=', $since)
            ->count();

        $this->summary = [
            'total_users' => $totalUsers,
            'enabled_users' => $enabledUsers,
            'disabled_users' => $disabledUsers,
            'active_users' => $activeUserIds,
            'inactive_users' => $inactiveCount,
            'active_percentage' => $activePercentage,
            'total_actions' => $totalActions,
        ];
    }

    protected function loadMostActiveUsers(Carbon $since): void
    {
        $rows = DB::table('activity_logs')
            ->select('user_id', DB::raw('COUNT(*) as action_count'))
            ->where('created_at', '>=', $since)
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('action_count')
            ->limit(10)
            ->get();

        $userIds = $rows->pluck('user_id')->toArray();
        $users = DB::table('users')
            ->whereIn('id', $userIds)
            ->get(['id', 'first_name', 'last_name', 'username', 'user_type', 'is_disabled'])
            ->keyBy('id');

        $lastActivity = DB::table('activity_logs')
            ->select('user_id', DB::raw('MAX(created_at) as last_active'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $this->mostActiveUsers = $rows->map(function ($row) use ($users, $lastActivity) {
            $user = $users->get($row->user_id);
            $last = $lastActivity->get($row->user_id);

            return [
                'id' => $row->user_id,
                'name' => $user ? trim($user->first_name . ' ' . $user->last_name) : 'Unknown',
                'username' => $user->username ?? 'N/A',
                'role' => $user ? $this->roleLabel((int) $user->user_type) : 'Unknown',
                'action_count' => (int) $row->action_count,
                'last_active' => $last ? Carbon::parse($last->last_active)->diffForHumans() : 'N/A',
                'is_disabled' => $user ? (bool) $user->is_disabled : false,
            ];
        })->toArray();
    }

    protected function loadInactiveUsers(Carbon $since): void
    {
        $activeUserIds = DB::table('activity_logs')
            ->where('created_at', '>=', $since)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        $query = DB::table('users')
            ->where('is_disabled', false)
            ->select('id', 'first_name', 'last_name', 'username', 'user_type', 'created_at');

        if (! empty($activeUserIds)) {
            $query->whereNotIn('id', $activeUserIds);
        }

        $users = $query->orderBy('created_at', 'asc')->limit(10)->get();

        $userIds = $users->pluck('id')->toArray();
        $lastActivity = DB::table('activity_logs')
            ->select('user_id', DB::raw('MAX(created_at) as last_active'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $this->inactiveUsers = $users->map(function ($user) use ($lastActivity) {
            $last = $lastActivity->get($user->id);

            return [
                'id' => $user->id,
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'username' => $user->username,
                'role' => $this->roleLabel((int) $user->user_type),
                'last_active' => $last ? Carbon::parse($last->last_active)->diffForHumans() : 'Never',
                'created_at' => Carbon::parse($user->created_at)->format('M d, Y'),
            ];
        })->toArray();
    }

    protected function loadActivityByDayChart(Carbon $since, int $days): void
    {
        $labels = [];
        $dateMap = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $since->copy()->addDays($i);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $dateMap[$key] = $i;
        }

        $rows = DB::table('activity_logs')
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $since)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        $data = array_fill(0, $days, 0);
        foreach ($rows as $row) {
            $idx = $dateMap[$row->day] ?? null;
            if ($idx !== null) {
                $data[$idx] = (int) $row->total;
            }
        }

        $this->activityByDayChart = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Actions',
                    'data' => $data,
                    'borderColor' => 'rgba(59,130,246,1)',
                    'backgroundColor' => 'rgba(59,130,246,0.15)',
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
        ];
    }

    protected function loadRoleDistributionChart(): void
    {
        $rows = DB::table('users')
            ->select('user_type', DB::raw('COUNT(*) as total'))
            ->groupBy('user_type')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            0 => 'rgba(168,85,247,0.75)',  // superadmin - purple
            1 => 'rgba(59,130,246,0.75)',   // admin - blue
            2 => 'rgba(16,185,129,0.75)',   // user - green
        ];
        $bgColors = [];

        foreach ($rows as $row) {
            $labels[] = $this->roleLabel((int) $row->user_type);
            $data[] = (int) $row->total;
            $bgColors[] = $colors[(int) $row->user_type] ?? 'rgba(107,114,128,0.75)';
        }

        $this->roleDistributionChart = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $bgColors,
                    'borderColor' => $bgColors,
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function loadTopActionsChart(Carbon $since): void
    {
        $rows = DB::table('activity_logs')
            ->select('action', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $since)
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $palette = [
            'rgba(59,130,246,0.75)',
            'rgba(16,185,129,0.75)',
            'rgba(234,179,8,0.75)',
            'rgba(239,68,68,0.75)',
            'rgba(168,85,247,0.75)',
            'rgba(20,184,166,0.75)',
            'rgba(249,115,22,0.75)',
            'rgba(99,102,241,0.75)',
        ];

        $this->topActionsChart = [
            'labels' => $rows->pluck('action')->map(fn ($a) => ucfirst(str_replace('_', ' ', $a)))->toArray(),
            'datasets' => [
                [
                    'label' => 'Count',
                    'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
                    'backgroundColor' => array_slice($palette, 0, $rows->count()),
                    'borderColor' => array_slice($palette, 0, $rows->count()),
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function roleLabel(int $type): string
    {
        return match ($type) {
            0 => 'Superadmin',
            1 => 'Admin',
            2 => 'Hatchery User',
            default => 'Unknown',
        };
    }

    public function render()
    {
        return view('livewire.admin.user-activity-dashboard');
    }
}
