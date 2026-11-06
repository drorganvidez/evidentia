/**
 * Deferred srcdoc frame of the catalogue: the document held in data-srcdoc is handed to the iframe the first
 * time the frame scrolls into view, so the pages framed in /_ui (three shells) cost nothing until they are seen.
 *
 * Blade: <div x-data="lazyFrame"><iframe title="..." data-srcdoc="{{ $html }}"></iframe></div>
 * The CSP build forbids Alpine expressions on an iframe, so the component lives on the wrapper.
 */
export default function lazyFrame() {
    return {
        observer: null,

        init() {
            const frame = this.$root.querySelector('iframe[data-srcdoc]');

            if (!frame) {
                return;
            }

            const load = () => {
                if (!frame.srcdoc) {
                    frame.srcdoc = frame.dataset.srcdoc;
                }
            };

            if (!('IntersectionObserver' in window)) {
                load();

                return;
            }

            this.observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    load();
                    this.observer.disconnect();
                }
            });
            this.observer.observe(frame);
        },

        destroy() {
            this.observer?.disconnect();
        },
    };
}
