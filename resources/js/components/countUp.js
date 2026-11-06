/**
 * Count-up of the figure in <x-ui.count-chip>: from zero to data-count with a cubic ease, formatted like the
 * server (thousands with a point, decimals with a comma). With reduced motion or a hidden tab the final
 * figure appears at once.
 *
 * Blade: <span x-data="countUp" data-count="1204" data-decimals="0" data-duration="1200" x-text="shown">1.204</span>
 */
function formatEs(value, decimals) {
    const [whole, fraction] = Math.abs(value).toFixed(decimals).split('.');
    const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return `${value < 0 ? '-' : ''}${grouped}${fraction ? `,${fraction}` : ''}`;
}

export default function countUp() {
    return {
        shown: '',
        frame: null,

        init() {
            const { count, decimals, duration } = this.$root.dataset;
            const end = Number(count) || 0;
            const places = Math.max(0, Number(decimals) || 0);
            const length = Math.max(0, Number(duration) || 0);
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (reduced || document.hidden || length === 0) {
                this.shown = formatEs(end, places);

                return;
            }

            const start = performance.now();
            const step = (now) => {
                const progress = Math.min(1, (now - start) / length);
                const eased = 1 - (1 - progress) ** 3;

                this.shown = formatEs(end * eased, places);

                if (progress < 1) {
                    this.frame = requestAnimationFrame(step);
                }
            };

            this.shown = formatEs(0, places);
            this.frame = requestAnimationFrame(step);
        },

        destroy() {
            cancelAnimationFrame(this.frame);
        },
    };
}
