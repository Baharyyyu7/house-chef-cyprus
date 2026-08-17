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
    const copyStatus = center.querySelector('[data-legal-copy-status]');
    center.querySelector('[data-legal-print]')?.addEventListener('click', () => window.print());
    center.querySelector('[data-copy-page]')?.addEventListener('click', async () => {
        const url = new URL(window.location.href);
        url.hash = '';
        try {
            await navigator.clipboard.writeText(url.toString());
            if (copyStatus) copyStatus.textContent = 'Sayfa bağlantısı kopyalandı.';
        } catch {
            window.prompt('Sayfa bağlantısını kopyalayın:', url.toString());
        }
    });
};

const mountPageFeatures = () => {
    mountAdminDashboard();
    mountLegalInformationCenter();
    // Ölçüm ve reklam onayı ayrı çerezlerde tutulur: ölçüme izin veren biri
    // reklama da izin vermiş sayılamaz. Reklam yuvası yalnız kendi çerezine
    // bakar, bu yüzden o çerezi verecek bir düğme olmadan hiçbir reklam çıkmaz.
    const bindConsent = (selector, datasetKey, cookieName) => {
        document.querySelectorAll(selector).forEach((button) => {
            if (button.dataset.consentBound === 'true') return;
            button.dataset.consentBound = 'true';
            button.addEventListener('click', () => {
                const choice = button.dataset[datasetKey] === 'granted' ? 'granted' : 'denied';
                const secure = window.location.protocol === 'https:' ? '; Secure' : '';
                document.cookie = `${cookieName}=${choice}; Path=/; Max-Age=31536000; SameSite=Lax${secure}`;
                window.location.reload();
            });
        });
    };

    bindConsent('[data-consent-choice]', 'consentChoice', 'hc_analytics_consent');
    bindConsent('[data-advertising-choice]', 'advertisingChoice', 'hc_advertising_consent');
};

document.addEventListener('DOMContentLoaded', mountPageFeatures);
document.addEventListener('livewire:navigated', mountPageFeatures);
window.addEventListener('hc-theme-changed', () => window.setTimeout(mountCharts, 0));

// Tarif adımlarını pişirirken işaretlemek için. Giriş gerektirmez; ilerleme
// yalnız kullanıcının cihazında, tarif başına ayrı bir anahtarda saklanır.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('hcRecipeSteps', (slug, total) => ({
        key: `hc-steps-${slug}`,
        total,
        done: [],
        init() {
            this.done = this.read().filter((value) => Number.isInteger(value) && value >= 1 && value <= this.total);
        },
        read() {
            try {
                const stored = JSON.parse(window.localStorage.getItem(this.key) ?? '[]');

                return Array.isArray(stored) ? stored : [];
            } catch {
                return [];
            }
        },
        persist() {
            try {
                window.localStorage.setItem(this.key, JSON.stringify(this.done));
            } catch {
                // Depolama kapalıysa ilerleme yalnız bu sayfa görüntülemesi boyunca yaşar.
            }
        },
        isDone(position) {
            return this.done.includes(position);
        },
        toggle(position) {
            this.done = this.isDone(position)
                ? this.done.filter((value) => value !== position)
                : [...this.done, position];
            this.persist();
        },
        reset() {
            this.done = [];
            this.persist();
        },
        get completed() {
            return this.done.length;
        },
        get percent() {
            return this.total > 0 ? Math.round((this.completed / this.total) * 100) : 0;
        },
        get label() {
            return this.completed > 0 ? `${this.completed} / ${this.total} adım` : `${this.total} adım`;
        },
    }));
});

// Malzeme seçimi: "Cmd/Ctrl ile çoklu seçim" yerine aranabilir çip listesi.
// Türkçe karakterler normalize edilir; "sogan" da "Soğan" da eşleşir.
const hcNormalize = (value) => (value ?? '')
    .toLocaleLowerCase('tr')
    .replaceAll('ı', 'i').replaceAll('ğ', 'g').replaceAll('ü', 'u')
    .replaceAll('ş', 's').replaceAll('ö', 'o').replaceAll('ç', 'c')
    .replaceAll('â', 'a').replaceAll('î', 'i').replaceAll('û', 'u')
    .replace(/[^a-z0-9 ]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

document.addEventListener('alpine:init', () => {
    window.Alpine.data('hcIngredientPicker', (options, preselected) => ({
        options,
        selected: Array.isArray(preselected) ? preselected.filter((id) => options.some((o) => o.id === id)) : [],
        query: '',
        highlighted: 0,
        get matches() {
            const needle = hcNormalize(this.query);

            if (needle === '') {
                return [];
            }

            return this.options
                .filter((option) => !this.selected.includes(option.id) && option.search.includes(needle))
                .slice(0, 8);
        },
        get chosen() {
            return this.selected
                .map((id) => this.options.find((option) => option.id === id))
                .filter(Boolean);
        },
        name(id) {
            return this.options.find((option) => option.id === id)?.name ?? '';
        },
        add(id) {
            if (id && !this.selected.includes(id)) {
                this.selected = [...this.selected, id];
            }
            this.query = '';
            this.highlighted = 0;
        },
        remove(id) {
            this.selected = this.selected.filter((value) => value !== id);
        },
        clear() {
            this.selected = [];
            this.query = '';
        },
        move(delta) {
            const total = this.matches.length;
            if (total === 0) {
                return;
            }
            this.highlighted = (this.highlighted + delta + total) % total;
        },
        commit() {
            const match = this.matches[this.highlighted];
            if (match) {
                this.add(match.id);
            }
        },
    }));
});
