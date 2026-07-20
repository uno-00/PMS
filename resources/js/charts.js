function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function chartColors() {
    const dark = isDarkMode();

    return {
        grid: dark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.35)',
        tick: dark ? '#94a3b8' : '#64748b',
        center: dark ? '#f8fafc' : '#0f172a',
        segmentBorder: dark ? '#0f172a' : '#ffffff',
        tooltipBg: dark ? '#0f172a' : '#ffffff',
        tooltipBorder: dark ? '#334155' : '#e2e8f0',
        tooltipText: dark ? '#f1f5f9' : '#0f172a',
        planned: '#0f766e',
        plannedFill: dark ? 'rgba(15, 118, 110, 0.22)' : 'rgba(15, 118, 110, 0.16)',
        actual: '#16a34a',
        actualFill: dark ? 'rgba(22, 163, 74, 0.18)' : 'rgba(22, 163, 74, 0.12)',
    };
}

const centerTextPlugin = {
    id: 'pmsCenterText',
    afterDraw(chart, _args, options) {
        const meta = chart.getDatasetMeta(0);
        if (! meta?.data?.length) {
            return;
        }

        const { ctx } = chart;
        const { x, y } = meta.data[0];
        const palette = chartColors();

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = palette.center;
        ctx.font = `700 ${options.valueSize ?? 28}px Inter, ui-sans-serif, system-ui, sans-serif`;
        ctx.fillText(String(options.value ?? '0'), x, y - 8);
        ctx.fillStyle = palette.tick;
        ctx.font = `500 ${options.labelSize ?? 11}px Inter, ui-sans-serif, system-ui, sans-serif`;
        ctx.fillText(options.label ?? 'Total cases', x, y + 14);
        ctx.restore();
    },
};

function baseScaleOptions() {
    const colors = chartColors();

    return {
        x: {
            grid: { display: false },
            border: { display: false },
            ticks: { color: colors.tick, font: { size: 11 } },
        },
        y: {
            beginAtZero: true,
            grid: { color: colors.grid, drawBorder: false },
            border: { display: false },
            ticks: {
                color: colors.tick,
                font: { size: 11 },
                callback: (value) => value,
            },
        },
    };
}

function baseTooltip() {
    const colors = chartColors();

    return {
        backgroundColor: colors.tooltipBg,
        borderColor: colors.tooltipBorder,
        borderWidth: 1,
        titleColor: colors.tooltipText,
        bodyColor: colors.tooltipText,
        padding: 12,
        cornerRadius: 10,
        displayColors: true,
        boxWidth: 10,
        boxHeight: 10,
        boxPadding: 4,
        callbacks: {
            label(context) {
                const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                const value = context.parsed ?? 0;
                const pct = total > 0 ? Math.round((value / total) * 100) : 0;

                return `${context.label}: ${value} (${pct}%)`;
            },
        },
    };
}

export function spendTracker(canvas, { planned = [], actual = [] } = {}) {
    const colors = chartColors();
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Planned',
                    data: planned,
                    borderColor: colors.planned,
                    backgroundColor: colors.plannedFill,
                    fill: true,
                    tension: 0.42,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: colors.planned,
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2,
                },
                {
                    label: 'Actual',
                    data: actual,
                    borderColor: colors.actual,
                    backgroundColor: colors.actualFill,
                    fill: true,
                    tension: 0.42,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: colors.actual,
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    align: 'start',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        color: colors.tick,
                        font: { size: 11, weight: '500' },
                    },
                },
                tooltip: baseTooltip(),
            },
            scales: baseScaleOptions(),
        },
    });
}

export function statusDonut(canvas, { labels = [], data = [], colors = [], total = 0, centerLabel = 'Total cases' } = {}) {
    const palette = chartColors();
    const fallbackColors = ['#64748b', '#d97706', '#4f46e5', '#7c3aed', '#0891b2', '#059669', '#2563eb', '#0284c7', '#16a34a', '#dc2626'];
    const segmentColors = data.map((_, index) => colors[index] ?? fallbackColors[index % fallbackColors.length]);

    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: segmentColors,
                borderColor: palette.segmentBorder,
                borderWidth: 3,
                hoverOffset: 8,
                spacing: 2,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '74%',
            layout: { padding: 4 },
            plugins: {
                legend: { display: false },
                tooltip: baseTooltip(),
                pmsCenterText: {
                    value: total,
                    label: centerLabel,
                },
            },
        },
        plugins: [centerTextPlugin],
    });
}

export function volumeBar(canvas, { data = [] } = {}) {
    const colors = chartColors();

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Purchase Requests',
                data,
                backgroundColor: colors.planned,
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 28,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: baseTooltip(),
            },
            scales: baseScaleOptions(),
        },
    });
}

window.PmsCharts = { spendTracker, statusDonut, volumeBar };
