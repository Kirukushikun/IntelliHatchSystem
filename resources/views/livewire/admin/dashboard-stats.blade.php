<div x-data="{ query: '' }">
    <!-- Header with Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-6">
        <div class="text-center sm:text-left">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Admin Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Welcome to admin dashboard.</p>
        </div>
        <div class="relative w-full sm:w-auto sm:shrink-0">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                type="text"
                x-model="query"
                placeholder="Search form types..."
                class="w-full pl-11 pr-4 py-3 text-sm bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 shadow-sm dark:shadow-md"
            />
        </div>
    </div>

    <!-- Stats Cards -->
    <div wire:poll.30s="refreshStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($cards as $card)
            <div x-show="!query || '{{ strtolower($card['type_name'] ?? '') }}'.includes(query.toLowerCase())" class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg dark:shadow-xl dark:hover:shadow-2xl transition-all duration-300 overflow-hidden group border border-l-4 border-gray-200 dark:border-gray-700 border-l-amber-500 cursor-pointer transform hover:scale-[1.02] hover:-translate-y-1">
                <a href="{{ match($card['type_name']) {
                    'Incubator Routine Checklist Per Shift' => 'incubator-routine-dashboard',
                    'Hatcher Blower Air Speed Monitoring' => 'blower-air-hatcher-dashboard',
                    'Incubator Blower Air Speed Monitoring' => 'blower-air-incubator-dashboard',
                    default => '#'
                } }}" class="block h-full">
                <!-- Card Header -->
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 mr-2">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $card['type_name'] }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Submission statistics</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card Content -->
                <div class="px-6 pb-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Week</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ $card['week'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Month</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ $card['month'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Year</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">{{ $card['year'] }}</div>
                        </div>
                    </div>
                </div>
                </a>
            </div>
        @endforeach
    </div>

    @if($showCharts)
    <!-- Chart Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Last 7 Days</h3>
            <div wire:ignore>
                <canvas id="adminDashboardWeekChart" height="160"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Last 30 Days</h3>
            <div wire:ignore>
                <canvas id="adminDashboardMonthChart" height="160"></canvas>
            </div>
        </div>
    </div>

    <!-- Full Width Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Last 12 Months</h3>
        <div class="max-w-2xl mx-auto" wire:ignore>
            <canvas id="adminDashboardYearChart" height="140"></canvas>
        </div>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endonce

    <script>
        (function () {
            const initialWeek = @js($charts['week']);
            const initialMonth = @js($charts['month']);
            const initialYear = @js($charts['year']);

            // Chart configurations with minimal styling
            const chartDefaults = {
                font: {
                    family: 'Inter, system-ui, -apple-system, sans-serif',
                    size: 12
                },
                color: '#6b7280'
            };

            function buildStackedBar(ctx, data) {
                return new Chart(ctx, {
                    type: 'bar',
                    data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    ...chartDefaults,
                                    padding: 16,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: '#1f2937',
                                padding: 12,
                                cornerRadius: 6,
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: { ...chartDefaults }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: '#f3f4f6' },
                                ticks: { ...chartDefaults, precision: 0 }
                            }
                        }
                    }
                });
            }

            function buildLine(ctx, data) {
                return new Chart(ctx, {
                    type: 'line',
                    data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    ...chartDefaults,
                                    padding: 16,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: '#1f2937',
                                padding: 12,
                                cornerRadius: 6,
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { ...chartDefaults }
                            },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: '#f3f4f6' },
                                ticks: { ...chartDefaults, precision: 0 }
                            }
                        },
                        elements: {
                            line: { tension: 0.3 },
                            point: { radius: 3, hoverRadius: 5 }
                        }
                    }
                });
            }

            function buildDoughnut(ctx, data) {
                return new Chart(ctx, {
                    type: 'doughnut',
                    data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    ...chartDefaults,
                                    padding: 16,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1f2937',
                                padding: 12,
                                cornerRadius: 6,
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 }
                            }
                        }
                    }
                });
            }

            function init() {
                if (!window.Chart) {
                    setTimeout(init, 50);
                    return;
                }

                // Set default font for all charts
                Chart.defaults.font.family = chartDefaults.font.family;

                window.__adminDashboardCharts = window.__adminDashboardCharts || {};

                const weekEl = document.getElementById('adminDashboardWeekChart');
                const monthEl = document.getElementById('adminDashboardMonthChart');
                const yearEl = document.getElementById('adminDashboardYearChart');

                if (!weekEl || !monthEl || !yearEl) return;

                // Destroy existing charts
                Object.values(window.__adminDashboardCharts).forEach((c) => {
                    try { c.destroy(); } catch (e) {}
                });

                // Create new charts
                window.__adminDashboardCharts.week = buildStackedBar(weekEl.getContext('2d'), initialWeek);
                window.__adminDashboardCharts.month = buildLine(monthEl.getContext('2d'), initialMonth);
                window.__adminDashboardCharts.year = buildDoughnut(yearEl.getContext('2d'), initialYear);
            }

            function updateCharts(charts) {
                if (!charts || !window.__adminDashboardCharts) return;

                ['week', 'month', 'year'].forEach(period => {
                    if (window.__adminDashboardCharts[period] && charts[period]) {
                        window.__adminDashboardCharts[period].data = charts[period];
                        window.__adminDashboardCharts[period].update('none'); // Faster update without animation
                    }
                });
            }

            // Initialize
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

            // Listen for updates
            window.addEventListener('dashboardStatsUpdated', (event) => {
                if (event?.detail?.charts) {
                    updateCharts(event.detail.charts);
                }
            });
        })();
    </script>
    @endif
</div>