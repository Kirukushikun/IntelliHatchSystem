import './bootstrap';
import Chart from 'chart.js/auto';

const CHART_COLORS = [
    '#f97316', '#3b82f6', '#22c55e', '#ef4444', '#a855f7',
    '#06b6d4', '#eab308', '#ec4899', '#14b8a6', '#f59e0b',
];

function isDarkMode() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

function chartDefaults(dark) {
    return {
        color: dark ? '#d1d5db' : '#374151',
        borderColor: dark ? '#374151' : '#e5e7eb',
        gridColor: dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)',
    };
}

function initCharts() {
    document.querySelectorAll('canvas[data-chart-config]').forEach(canvas => {
        // Skip already-initialized canvases
        if (canvas.dataset.chartInit) return;

        let config;
        try {
            config = JSON.parse(canvas.dataset.chartConfig);
        } catch {
            return;
        }

        if (!config.type || !config.labels || !config.datasets) return;

        const dark = isDarkMode();
        const defaults = chartDefaults(dark);

        // Assign colors to datasets
        config.datasets.forEach((ds, i) => {
            const color = CHART_COLORS[i % CHART_COLORS.length];
            if (['pie', 'doughnut', 'polarArea'].includes(config.type)) {
                ds.backgroundColor = ds.backgroundColor || config.labels.map((_, j) => CHART_COLORS[j % CHART_COLORS.length] + 'cc');
                ds.borderColor = ds.borderColor || (dark ? '#1f2937' : '#ffffff');
                ds.borderWidth = ds.borderWidth ?? 2;
            } else if (config.type === 'line') {
                ds.borderColor = ds.borderColor || color;
                ds.backgroundColor = ds.backgroundColor || color + '33';
                ds.tension = ds.tension ?? 0.3;
                ds.fill = ds.fill ?? true;
                ds.pointRadius = ds.pointRadius ?? 3;
            } else if (config.type === 'radar') {
                ds.borderColor = ds.borderColor || color;
                ds.backgroundColor = ds.backgroundColor || color + '33';
                ds.pointBackgroundColor = ds.pointBackgroundColor || color;
            } else {
                // bar and others
                ds.backgroundColor = ds.backgroundColor || color + 'cc';
                ds.borderColor = ds.borderColor || color;
                ds.borderWidth = ds.borderWidth ?? 1;
                ds.borderRadius = ds.borderRadius ?? 4;
            }
        });

        const isPieType = ['pie', 'doughnut', 'polarArea'].includes(config.type);

        new Chart(canvas, {
            type: config.type,
            data: {
                labels: config.labels,
                datasets: config.datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    title: config.title ? {
                        display: true,
                        text: config.title,
                        color: defaults.color,
                        font: { size: 14, weight: '600' },
                        padding: { bottom: 12 },
                    } : { display: false },
                    legend: {
                        display: config.datasets.length > 1 || isPieType,
                        labels: { color: defaults.color, usePointStyle: true, padding: 12 },
                    },
                },
                scales: isPieType ? {} : {
                    x: {
                        ticks: { color: defaults.color },
                        grid: { color: defaults.gridColor },
                        border: { color: defaults.borderColor },
                    },
                    y: {
                        ticks: { color: defaults.color },
                        grid: { color: defaults.gridColor },
                        border: { color: defaults.borderColor },
                        beginAtZero: true,
                    },
                },
            },
        });

        canvas.dataset.chartInit = '1';
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initCharts);

// Re-initialize after Livewire updates (translation toggle, poll refresh)
document.addEventListener('livewire:navigated', initCharts);

// Livewire 3/4 morphing — fires after each component update
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', () => {
        setTimeout(initCharts, 50);
    });
});
