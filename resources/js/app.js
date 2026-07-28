// Livewire ships and starts Alpine for every layout in this application.
// Starting a second copy from Vite breaks Livewire's navigate redirect contract.
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const chartInstances = new WeakMap();

const cssVariable = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

const palette = () => [
    cssVariable('--hc-brand-primary'),
    '#fb923c',
    '#fbbf24',
    '#60a5fa',
    '#c084fc',
    '#f472b6',
    '#2dd4bf',
    '#a3e635',
];

const chartConfig = (data) => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const dark = document.documentElement.classList.contains('dark');
    const text = cssVariable('--hc-text-secondary');
    const grid = cssVariable('--hc-border-subtle');
    const colors = palette();
    const isDonut = data.type === 'doughnut';

    return {
        type: data.type,
        data: {
            labels: data.labels,
            datasets: [{
                label: data.title,
                data: data.values,
                backgroundColor: isDonut ? colors : `${colors[0]}cc`,
                borderColor: isDonut ? (dark ? '#171717' : '#fffdfc') : colors[0],
                borderWidth: isDonut ? 3 : 2,
                borderRadius: data.type === 'bar' ? 8 : 0,
                tension: data.type === 'line' ? 0.34 : 0,
                fill: data.type === 'line',
                pointRadius: data.type === 'line' ? 3 : 0,
                pointHoverRadius: data.type === 'line' ? 5 : 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: reducedMotion ? false : { duration: 420, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: isDonut, position: 'bottom', labels: { color: text, boxWidth: 10, padding: 14, font: { family: 'Inter' } } },
                tooltip: { backgroundColor: dark ? '#0b0b0c' : '#171717', titleColor: '#fffaf5', bodyColor: '#fffaf5', padding: 11, cornerRadius: 10 },
            },
            scales: isDonut ? {} : {
                x: { grid: { display: false }, ticks: { color: text, maxRotation: 0, autoSkipPadding: 14, font: { family: 'Inter' } } },
                y: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { color: text, precision: 0, font: { family: 'Inter' } } },
            },
        },
    };
};

const mountCharts = () => {
    document.querySelectorAll('[data-admin-chart]').forEach((canvas) => {
        let data;
        try {
            data = JSON.parse(canvas.dataset.adminChartConfig || '{}');
        } catch {
            return;
        }

        if (!data.type || !Array.isArray(data.labels) || !Array.isArray(data.values)) return;
        chartInstances.get(canvas)?.destroy();
        chartInstances.set(canvas, new Chart(canvas, chartConfig(data)));
    });
};

const animateCounts = () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    document.querySelectorAll('[data-hc-count]').forEach((element) => {
        const target = Number(element.dataset.hcCount);
        if (!Number.isFinite(target) || element.dataset.counted === 'true') return;
        element.dataset.counted = 'true';
        const started = performance.now();
        const duration = 420;
        const suffix = element.textContent.replace(/[\d.,\s]/g, '');
        const update = (now) => {
            const progress = Math.min(1, (now - started) / duration);
            const value = Math.round(target * (1 - Math.pow(1 - progress, 3)));
            element.textContent = `${new Intl.NumberFormat('tr-TR').format(value)}${suffix}`;
            if (progress < 1) requestAnimationFrame(update);
        };
        requestAnimationFrame(update);
    });
};

const mountAdminDashboard = () => {
    mountCharts();
    animateCounts();
};

document.addEventListener('DOMContentLoaded', mountAdminDashboard);
document.addEventListener('livewire:navigated', mountAdminDashboard);
window.addEventListener('hc-theme-changed', () => window.setTimeout(mountCharts, 0));
