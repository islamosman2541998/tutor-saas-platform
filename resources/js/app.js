import Swal from 'sweetalert2';
import './theme.js';

const toast = Swal.mixin({
    toast: true,
    position: 'top-start',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (el) => {
        el.addEventListener('mouseenter', Swal.stopTimer);
        el.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

/**
 * Chart containers carry their ApexCharts options as a JSON `data-chart`
 * attribute (see the dashboard view) — ApexCharts itself is only pulled in
 * via dynamic import when at least one such container actually exists on
 * the page, so pages without charts (the vast majority) never download it.
 *
 * Options are authored server-side assuming a light background, so dark
 * mode overrides the few colors that would otherwise be illegible (grid
 * lines, axis/legend labels, tooltip theme) right before construction —
 * re-run wholesale (destroy + recreate) on 'theme-changed' so an open
 * dashboard reflects a toggle click immediately, not just after reload.
 */
function applyChartTheme(options) {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? '#334155' : '#e2e8f0';
    const labelColor = isDark ? '#94a3b8' : '#64748b';

    options.theme = { ...(options.theme ?? {}), mode: isDark ? 'dark' : 'light' };
    options.tooltip = { ...(options.tooltip ?? {}), theme: isDark ? 'dark' : 'light' };
    options.grid = { ...(options.grid ?? {}), borderColor: gridColor };
    options.xaxis = { ...(options.xaxis ?? {}), labels: { ...(options.xaxis?.labels ?? {}), style: { colors: labelColor } } };
    options.yaxis = { ...(options.yaxis ?? {}), labels: { ...(options.yaxis?.labels ?? {}), style: { colors: labelColor } } };
    options.legend = { ...(options.legend ?? {}), labels: { colors: labelColor } };

    return options;
}

async function initCharts() {
    const containers = document.querySelectorAll('[data-chart]');
    if (containers.length === 0) {
        return;
    }

    const { default: ApexCharts } = await import('apexcharts');

    containers.forEach((el) => {
        if (el._chart) {
            el._chart.destroy();
        }
        const options = applyChartTheme(JSON.parse(el.dataset.chart));
        el._chart = new ApexCharts(el, options);
        el._chart.render();
    });
}

document.addEventListener('DOMContentLoaded', initCharts);
document.addEventListener('livewire:navigated', initCharts);
window.addEventListener('theme-changed', initCharts);

document.addEventListener('livewire:init', () => {
    /**
     * PHP: $this->dispatch('toast', message: 'تم الحفظ بنجاح', type: 'success');
     */
    Livewire.on('toast', ({ message, type = 'success' }) => {
        toast.fire({ icon: type, title: message });
    });

    /**
     * PHP: $this->dispatch('confirm', title: '...', text: '...', confirmEvent: 'delete-confirmed', params: {id: 1});
     * On confirm, re-dispatches `confirmEvent` back to the same component with `params`.
     */
    Livewire.on('confirm', ({ title, text = '', confirmButtonText = 'تأكيد', confirmEvent, params = {} }) => {
        Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch(confirmEvent, params);
            }
        });
    });
});
