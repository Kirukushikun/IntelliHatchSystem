<div x-data="{ query: '', view: 'charts' }" x-init="$watch('query', (q) => window.dispatchEvent(new CustomEvent('dashboardQueryChanged', { detail: { query: q, view: view } }))); $watch('view', (v) => window.dispatchEvent(new CustomEvent('dashboardQueryChanged', { detail: { query: query, view: v } })))">
    <!-- Header with Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div class="text-center sm:text-left">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Admin Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Welcome to admin dashboard.</p>
        </div>
        <div class="flex w-full sm:w-auto sm:shrink-0 gap-2">
            <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <button type="button" @click="view = 'charts'" :class="view === 'charts' ? 'bg-blue-600 text-white' : 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'" class="px-3 py-2 text-xs font-medium transition-colors">
                    Charts
                </button>
                <button type="button" @click="view = 'cards'" :class="view === 'cards' ? 'bg-blue-600 text-white' : 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'" class="px-3 py-2 text-xs font-medium transition-colors">
                    Dashboards
                </button>
            </div>

            <div class="relative w-full sm:w-80">
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
    </div>

    <div x-data="{ open: false, typeQuery: '' }" x-show="view === 'charts'" x-transition class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
            <button type="button" @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Forms Filter</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ count($selectedTypes) }} Forms Selected
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition class="px-4 pb-4">
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between mb-3">
                    <div class="relative w-full sm:max-w-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input
                            type="text"
                            x-model="typeQuery"
                            placeholder="Filter types..."
                            class="w-full pl-9 pr-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent placeholder:text-gray-400 dark:placeholder:text-gray-500"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="selectAllTypes" class="px-3 py-2 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">
                            Select all
                        </button>
                        <button type="button" wire:click="clearSelectedTypes" class="px-3 py-2 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="max-h-56 overflow-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($typeOptions as $opt)
                            <label
                                wire:key="dashboard-type-opt-{{ $opt['id'] }}"
                                x-show="!typeQuery || '{{ strtolower($opt['name']) }}'.includes(typeQuery.toLowerCase())"
                                class="flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    value="{{ (string) $opt['id'] }}"
                                    wire:model.live="selectedTypes"
                                    class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                />
                                <span class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $opt['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($showCharts)
    <div x-show="view === 'charts'" x-transition class="mb-4">
    <!-- Chart Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Last 7 Days</h3>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Live</span>
                </div>
            </div>
            <div class="relative h-64" wire:ignore>
                <canvas id="adminDashboardWeekChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Last 30 Days</h3>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Live</span>
                </div>
            </div>
            <div class="relative h-64" wire:ignore>
                <canvas id="adminDashboardMonthChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Full Width Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Last 12 Months</h3>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Live</span>
            </div>
        </div>
        <div class="relative h-80" wire:ignore>
            <canvas id="adminDashboardYearChart"></canvas>
        </div>
    </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div x-show="view === 'cards'" x-transition>
    <div wire:poll.30s="refreshStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($cards as $card)
            <div x-show="(!query || '{{ strtolower($card['type_name'] ?? '') }}'.includes(query.toLowerCase()))" class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg dark:shadow-xl dark:hover:shadow-2xl transition-all duration-300 overflow-hidden group border border-l-4 border-gray-200 dark:border-gray-700 border-l-amber-500 cursor-pointer transform hover:scale-[1.02] hover:-translate-y-1">
                <a href="{{ match($card['type_name']) {
                    'Incubator Routine Checklist Per Shift' => 'incubator-routine-dashboard',
                    'Hatcher Blower Air Speed Monitoring' => 'blower-air-hatcher-dashboard',
                    'Incubator Blower Air Speed Monitoring' => 'blower-air-incubator-dashboard',
                    'Hatchery Sullair Air Compressor Weekly PMS Checklist' => 'hatchery-sullair-dashboard',
                    default => '#'
                } }}" class="block h-full">
                <!-- Card Header -->
                <div class="px-4 py-3">
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
                <div class="px-4 pb-3">
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
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endonce

    <script>
        (function () {
            let currentQuery = '';
            let currentView = 'charts';
            let baseCharts = null;

            // Debug: Check if charts data exists
            const chartsData = @json($charts);
            console.log('Charts data:', chartsData);
            console.log('ShowCharts:', @json($showCharts));
            
            // Ensure Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }
            
            const initialWeek = chartsData.week || { labels: [], datasets: [] };
            const initialMonth = chartsData.month || { labels: [], datasets: [] };
            const initialYear = chartsData.year || { labels: [], datasets: [] };
            
            console.log('Week data:', initialWeek);
            console.log('Month data:', initialMonth);
            console.log('Year data:', initialYear);

            function normalize(str) {
                return String(str || '').toLowerCase();
            }

            function filterChartDataByQuery(charts, query, view) {
                if (!charts) return charts;

                if (view !== 'charts') {
                    return charts;
                }

                const q = normalize(query).trim();
                if (!q) {
                    return charts;
                }

                const filtered = {
                    week: { labels: charts.week?.labels || [], datasets: [] },
                    month: { labels: charts.month?.labels || [], datasets: [] },
                    year: { labels: [], datasets: [] },
                };

                const weekDatasets = (charts.week?.datasets || []).filter(ds => normalize(ds.label).includes(q));
                const monthDatasets = (charts.month?.datasets || []).filter(ds => normalize(ds.label).includes(q));

                filtered.week.datasets = weekDatasets;
                filtered.month.datasets = monthDatasets;

                const yearLabels = charts.year?.labels || [];
                const yearDataset = (charts.year?.datasets || [])[0] || null;
                if (yearDataset && Array.isArray(yearDataset.data)) {
                    const kept = [];
                    yearLabels.forEach((lbl, idx) => {
                        if (normalize(lbl).includes(q)) {
                            kept.push({
                                label: lbl,
                                value: yearDataset.data[idx],
                                bg: Array.isArray(yearDataset.backgroundColor) ? yearDataset.backgroundColor[idx] : yearDataset.backgroundColor,
                                border: Array.isArray(yearDataset.borderColor) ? yearDataset.borderColor[idx] : yearDataset.borderColor,
                            });
                        }
                    });

                    filtered.year.labels = kept.map(k => k.label);
                    filtered.year.datasets = [{
                        ...yearDataset,
                        data: kept.map(k => k.value),
                        backgroundColor: kept.map(k => k.bg),
                        borderColor: kept.map(k => k.border),
                    }];
                } else {
                    filtered.year = charts.year;
                }

                return filtered;
            }

            // Chart configurations with Flowbite-inspired styling
            const chartDefaults = {
                font: {
                    family: 'Inter, system-ui, -apple-system, sans-serif',
                    size: 12
                },
                color: '#6b7280'
            };

            // Modern color palette inspired by Flowbite
            const colors = {
                primary: ['#3b82f6', '#2563eb', '#1d4ed8', '#1e40af'],
                success: ['#10b981', '#059669', '#047857', '#065f46'],
                warning: ['#f59e0b', '#d97706', '#b45309', '#92400e'],
                danger: ['#ef4444', '#dc2626', '#b91c1c', '#991b1b'],
                purple: ['#8b5cf6', '#7c3aed', '#6d28d9', '#5b21b6'],
                gray: ['#6b7280', '#4b5563', '#374151', '#1f2937']
            };

            function buildStackedBar(ctx, data) {
                return new Chart(ctx, {
                    type: 'bar',
                    data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    ...chartDefaults,
                                    padding: 16,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 },
                                displayColors: true,
                                boxPadding: 4
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: { ...chartDefaults, font: { size: 11 } }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                border: { display: false },
                                grid: { 
                                    color: 'rgba(107, 114, 128, 0.1)',
                                    drawBorder: false
                                },
                                ticks: { ...chartDefaults, font: { size: 11 }, precision: 0 }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
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
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    ...chartDefaults,
                                    padding: 16,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 },
                                displayColors: true,
                                boxPadding: 4
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { ...chartDefaults, font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { 
                                    color: 'rgba(107, 114, 128, 0.1)',
                                    drawBorder: false
                                },
                                ticks: { ...chartDefaults, font: { size: 11 }, precision: 0 }
                            }
                        },
                        elements: {
                            line: { tension: 0.4, borderWidth: 3 },
                            point: { radius: 4, hoverRadius: 6, borderWidth: 2 }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
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
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    ...chartDefaults,
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: { size: 13, weight: '600' },
                                bodyFont: { size: 12 },
                                displayColors: true,
                                boxPadding: 4
                            }
                        },
                        elements: {
                            arc: {
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        }
                    }
                });
            }

            function init() {
                console.log('Initializing charts...');
                
                // Ensure Chart.js is loaded
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js not loaded');
                    return;
                }

                // Set default font for all charts
                Chart.defaults.font.family = chartDefaults.font.family;

                window.__adminDashboardCharts = window.__adminDashboardCharts || {};

                const weekEl = document.getElementById('adminDashboardWeekChart');
                const monthEl = document.getElementById('adminDashboardMonthChart');
                const yearEl = document.getElementById('adminDashboardYearChart');

                console.log('Chart elements:', { weekEl, monthEl, yearEl });

                if (!weekEl || !monthEl || !yearEl) {
                    console.error('Chart canvas elements not found');
                    return;
                }

                // Create simple test data if no data exists
                if (!initialWeek.labels || initialWeek.labels.length === 0) {
                    console.warn('No week data available, using test data');
                    initialWeek.labels = ['Week 1', 'Week 2', 'Week 3'];
                    initialWeek.datasets = [{
                        label: 'Incubator Routine',
                        data: [45, 52, 38],
                        backgroundColor: colors.primary[0],
                        borderColor: colors.primary[1],
                        borderWidth: 2
                    }, {
                        label: 'Hatcher Blower',
                        data: [28, 35, 42],
                        backgroundColor: colors.success[0],
                        borderColor: colors.success[1],
                        borderWidth: 2
                    }, {
                        label: 'Incubator Blower',
                        data: [15, 22, 18],
                        backgroundColor: colors.warning[0],
                        borderColor: colors.warning[1],
                        borderWidth: 2
                    }];
                }

                if (!initialMonth.labels || initialMonth.labels.length === 0) {
                    console.warn('No month data available, using test data');
                    initialMonth.labels = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'];
                    initialMonth.datasets = [{
                        label: 'Incubator Routine',
                        data: [12, 19, 15, 22, 18],
                        borderColor: colors.primary[0],
                        backgroundColor: colors.primary[0] + '20',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: colors.primary[0],
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }, {
                        label: 'Hatcher Blower',
                        data: [8, 12, 10, 15, 14],
                        borderColor: colors.success[0],
                        backgroundColor: colors.success[0] + '20',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: colors.success[0],
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }];
                }

                if (!initialYear.labels || initialYear.labels.length === 0) {
                    console.warn('No year data available, using test data');
                    initialYear.labels = ['Incubator Routine', 'Hatcher Blower', 'Incubator Blower'];
                    initialYear.datasets = [{
                        data: [1250, 850, 420],
                        backgroundColor: [colors.primary[0], colors.success[0], colors.warning[0]],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 8
                    }];
                }

                baseCharts = { week: initialWeek, month: initialMonth, year: initialYear };
                const chartsForView = filterChartDataByQuery(baseCharts, currentQuery, currentView);
                console.log('Creating charts with data:', chartsForView);

                try {
                    // Create new charts
                    window.__adminDashboardCharts.week = buildStackedBar(weekEl.getContext('2d'), chartsForView.week);
                    console.log('Week chart created');
                    
                    window.__adminDashboardCharts.month = buildLine(monthEl.getContext('2d'), chartsForView.month);
                    console.log('Month chart created');
                    
                    window.__adminDashboardCharts.year = buildDoughnut(yearEl.getContext('2d'), chartsForView.year);
                    console.log('Year chart created');
                } catch (error) {
                    console.error('Error creating charts:', error);
                }
            }

            function updateCharts(charts) {
                if (!charts || !window.__adminDashboardCharts) return;

                baseCharts = charts;
                const chartsForView = filterChartDataByQuery(baseCharts, currentQuery, currentView);

                ['week', 'month', 'year'].forEach(period => {
                    if (window.__adminDashboardCharts[period] && chartsForView[period]) {
                        window.__adminDashboardCharts[period].data = chartsForView[period];
                        window.__adminDashboardCharts[period].update();
                    }
                });
            }

            // Listen for Livewire updates using window.addEventListener as fallback
            document.addEventListener('livewire:initialized', () => {
                console.log('Livewire initialized');
            });

            // Listen for Livewire updates using dispatch event
            window.addEventListener('dashboardStatsUpdated', (event) => {
                if (event.detail && event.detail.charts) {
                    console.log('Received chart update:', event.detail.charts);
                    updateCharts(event.detail.charts);
                }
            });

            window.addEventListener('dashboardQueryChanged', (event) => {
                const q = event?.detail?.query ?? '';
                const v = event?.detail?.view ?? 'charts';
                currentQuery = q;
                currentView = v;

                if (!baseCharts || !window.__adminDashboardCharts) return;

                const chartsForView = filterChartDataByQuery(baseCharts, currentQuery, currentView);
                ['week', 'month', 'year'].forEach(period => {
                    if (window.__adminDashboardCharts[period]) {
                        window.__adminDashboardCharts[period].data = chartsForView[period];
                        window.__adminDashboardCharts[period].update();
                    }
                });
            });

            // Fallback: Also try to hook system
            if (typeof Livewire !== 'undefined') {
                Livewire.hook('message.processed', (message, component) => {
                    if (component.name === 'admin.dashboard-stats') {
                        // Check if the message contains chart updates
                        if (message.effects && message.effects.dispatches) {
                            const dispatch = message.effects.dispatches.find(d => d.event === 'dashboardStatsUpdated');
                            if (dispatch && dispatch.params && dispatch.params.charts) {
                                updateCharts(dispatch.params.charts);
                            }
                        }
                    }
                });
            }

            // Initialize
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
</div>