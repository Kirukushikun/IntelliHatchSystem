<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Activity Dashboard</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Monitor user engagement and activity trends.</p>
        </div>
        <div>
            <select wire:model.live="period" class="px-4 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent text-gray-700 dark:text-gray-200 shadow-sm">
                <option value="7">Last 7 days</option>
                <option value="14">Last 14 days</option>
                <option value="30">Last 30 days</option>
                <option value="60">Last 60 days</option>
                <option value="90">Last 90 days</option>
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Users -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Users</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $summary['total_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active Users</p>
                    <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $summary['active_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Inactive Users -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Inactive Users</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ $summary['inactive_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Active Percentage -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active %</p>
                    <p class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $summary['active_percentage'] ?? 0 }}%</p>
                </div>
            </div>
        </div>

        <!-- Disabled Users -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Disabled Users</p>
                    <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $summary['disabled_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Enabled Users -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-900/30">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Enabled Users</p>
                    <p class="text-xl font-bold text-teal-600 dark:text-teal-400">{{ $summary['enabled_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Total Actions -->
        <div class="col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Actions (period)</p>
                    <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($summary['total_actions'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Activity Over Time (spans 2 cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Activity Over Time</h3>
            <div class="relative h-64" wire:ignore>
                <canvas id="activityByDayChart"></canvas>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">User Role Distribution</h3>
            <div class="relative h-64" wire:ignore>
                <canvas id="roleDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Actions Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top Actions</h3>
        <div class="relative h-64" wire:ignore>
            <canvas id="topActionsChart"></canvas>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Most Active Users -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Most Active Users</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Top 10 by action count in selected period</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Last Active</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($mostActiveUsers as $index => $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user['username'] }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($user['role']) {
                                            'Superadmin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                            'Admin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                            default => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        } }}">
                                        {{ $user['role'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($user['action_count']) }}</td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-gray-400">{{ $user['last_active'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No activity found in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inactive Users -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Inactive Users</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Enabled users with no activity in selected period</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Last Active</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($inactiveUsers as $index => $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user['username'] }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($user['role']) {
                                            'Superadmin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                            'Admin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                            default => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        } }}">
                                        {{ $user['role'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-gray-400">{{ $user['last_active'] }}</td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-gray-400">{{ $user['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">All enabled users are active!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endonce

    <script>
        (function () {
            const activityByDayData = @json($activityByDayChart);
            const roleDistributionData = @json($roleDistributionChart);
            const topActionsData = @json($topActionsChart);

            const chartDefaults = {
                font: { family: 'Inter, system-ui, -apple-system, sans-serif', size: 12 },
                color: '#6b7280'
            };

            function init() {
                if (typeof Chart === 'undefined') return;

                Chart.defaults.font.family = chartDefaults.font.family;
                window.__userActivityCharts = window.__userActivityCharts || {};

                // Destroy existing charts
                Object.values(window.__userActivityCharts).forEach(c => c && c.destroy());

                const activityEl = document.getElementById('activityByDayChart');
                const roleEl = document.getElementById('roleDistributionChart');
                const actionsEl = document.getElementById('topActionsChart');

                if (activityEl) {
                    window.__userActivityCharts.activity = new Chart(activityEl.getContext('2d'), {
                        type: 'line',
                        data: activityByDayData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(17,24,39,0.95)',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: { size: 13, weight: '600' },
                                    bodyFont: { size: 12 },
                                }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { ...chartDefaults, font: { size: 11 } } },
                                y: {
                                    beginAtZero: true,
                                    border: { display: false },
                                    grid: { color: 'rgba(107,114,128,0.1)' },
                                    ticks: { ...chartDefaults, font: { size: 11 }, precision: 0 }
                                }
                            },
                            elements: {
                                line: { tension: 0.3, borderWidth: 3 },
                                point: { radius: 3, hoverRadius: 6, borderWidth: 2 }
                            }
                        }
                    });
                }

                if (roleEl) {
                    window.__userActivityCharts.role = new Chart(roleEl.getContext('2d'), {
                        type: 'doughnut',
                        data: roleDistributionData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { ...chartDefaults, padding: 16, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(17,24,39,0.95)',
                                    padding: 12,
                                    cornerRadius: 8,
                                }
                            },
                            elements: { arc: { borderWidth: 2, borderColor: '#ffffff' } }
                        }
                    });
                }

                if (actionsEl) {
                    window.__userActivityCharts.actions = new Chart(actionsEl.getContext('2d'), {
                        type: 'bar',
                        data: topActionsData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(17,24,39,0.95)',
                                    padding: 12,
                                    cornerRadius: 8,
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    border: { display: false },
                                    grid: { color: 'rgba(107,114,128,0.1)' },
                                    ticks: { ...chartDefaults, font: { size: 11 }, precision: 0 }
                                },
                                y: { grid: { display: false }, ticks: { ...chartDefaults, font: { size: 11 } } }
                            }
                        }
                    });
                }
            }

            window.addEventListener('userActivityChartsUpdated', (event) => {
                if (!event.detail || !window.__userActivityCharts) return;
                const charts = event.detail.charts || event.detail;

                if (charts.activityByDay && window.__userActivityCharts.activity) {
                    window.__userActivityCharts.activity.data = charts.activityByDay;
                    window.__userActivityCharts.activity.update();
                }
                if (charts.roleDistribution && window.__userActivityCharts.role) {
                    window.__userActivityCharts.role.data = charts.roleDistribution;
                    window.__userActivityCharts.role.update();
                }
                if (charts.topActions && window.__userActivityCharts.actions) {
                    window.__userActivityCharts.actions.data = charts.topActions;
                    window.__userActivityCharts.actions.update();
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
</div>
