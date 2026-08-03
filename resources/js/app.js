// Livewire ships and starts Alpine for every layout in this application.
// Starting a second copy from Vite breaks Livewire's navigate redirect contract.
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const chartInstances = new WeakMap();

const cssVariable = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

const palette = () => [
    cssVariable('--hc-chart-1'),
    cssVariable('--hc-chart-2'),
    cssVariable('--hc-chart-3'),
    cssVariable('--hc-chart-4'),
    cssVariable('--hc-chart-5'),
];

const chartConfig = (data) => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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
                borderColor: isDonut ? cssVariable('--hc-chart-surface') : colors[0],
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
                tooltip: { backgroundColor: cssVariable('--hc-surface-overlay'), titleColor: cssVariable('--hc-text-primary'), bodyColor: cssVariable('--hc-text-secondary'), borderColor: cssVariable('--hc-border-default'), borderWidth: 1, padding: 11, cornerRadius: 10 },
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

const mountLegalInformationCenter = () => {
    const center = document.querySelector('[data-legal-center]');
    if (!center || center.dataset.initialized === 'true') return;

    center.dataset.initialized = 'true';
    const sections = [...center.querySelectorAll('[data-legal-section]')];
    const links = [...center.querySelectorAll('[data-legal-nav-link]')];
    const search = center.querySelector('[data-legal-search]');
    const searchStatus = center.querySelector('[data-legal-search-status]');
    const select = center.querySelector('[data-legal-section-select]');
    const copyStatus = center.querySelector('[data-legal-copy-status]');

    const activate = (id) => {
        links.forEach((link) => link.classList.toggle('bg-orange-100', link.dataset.sectionId === id));
        links.forEach((link) => link.classList.toggle('text-orange-800', link.dataset.sectionId === id));
        links.forEach((link) => link.classList.toggle('dark:bg-orange-500/20', link.dataset.sectionId === id));
        links.forEach((link) => link.classList.toggle('dark:text-orange-100', link.dataset.sectionId === id));
        if (select && select.value !== id) select.value = id;
    };

    search?.addEventListener('input', () => {
        const query = search.value.trim().toLocaleLowerCase('tr-TR');
        let visible = 0;
        sections.forEach((section) => {
            const matches = query === '' || section.textContent.toLocaleLowerCase('tr-TR').includes(query);
            section.hidden = !matches;
            if (matches) visible += 1;
        });
        if (searchStatus) searchStatus.textContent = query === '' ? '' : `${visible} bölüm bulundu.`;
    });

    select?.addEventListener('change', () => document.getElementById(select.value)?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    center.querySelector('[data-legal-print]')?.addEventListener('click', () => window.print());

    center.querySelectorAll('[data-copy-section]').forEach((button) => button.addEventListener('click', async () => {
        const url = new URL(window.location.href);
        url.hash = button.dataset.sectionId || '';
        try {
            await navigator.clipboard.writeText(url.toString());
            if (copyStatus) copyStatus.textContent = 'Bölüm bağlantısı kopyalandı.';
        } catch {
            window.prompt('Bölüm bağlantısını kopyalayın:', url.toString());
        }
    }));

    const observer = new IntersectionObserver((entries) => {
        const active = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (active) activate(active.target.id);
    }, { rootMargin: '-18% 0px -68% 0px', threshold: [0.05, 0.3] });
    sections.forEach((section) => observer.observe(section));
    activate(window.location.hash.slice(1) || sections[0]?.id);
};

const mountPageFeatures = () => {
    mountAdminDashboard();
    mountLegalInformationCenter();
};

document.addEventListener('DOMContentLoaded', mountPageFeatures);
document.addEventListener('livewire:navigated', mountPageFeatures);
window.addEventListener('hc-theme-changed', () => window.setTimeout(mountCharts, 0));
