import Swal from 'sweetalert2';

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
 */
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
        const options = JSON.parse(el.dataset.chart);
        el._chart = new ApexCharts(el, options);
        el._chart.render();
    });
}

document.addEventListener('DOMContentLoaded', initCharts);
document.addEventListener('livewire:navigated', initCharts);

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
