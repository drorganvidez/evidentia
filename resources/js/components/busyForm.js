/**
 * Busy state of <x-ui.form>: blocks the double submit, relabels the pressed submit button and frees
 * itself when the page comes back from the history cache or, for downloads, after a few seconds.
 * A submit that another listener cancels (a validation of its own) leaves the form free.
 *
 * Blade: <form x-data="busyForm" data-busy-label="Guardando" x-on:submit="submit"
 * x-bind:aria-busy="busy ? 'true' : 'false'"> with data-download when the response is a file.
 * A submit button may carry its own data-busy-label. Every access to window and the DOM lives here.
 */
const DOWNLOAD_RELEASE = 8000;

function textNodes(button) {
    return Array.from(button.childNodes).filter(
        (node) => node.nodeType === Node.TEXT_NODE && node.nodeValue.trim() !== '',
    );
}

/** Replaces the visible text of a button and keeps its icon; an icon-only button is left alone. */
function setLabel(button, text) {
    const nodes = textNodes(button);

    nodes.forEach((node, index) => {
        node.nodeValue = index === 0 ? text : '';
    });
}

export default function busyForm() {
    const originals = new Map();

    return {
        busy: false,
        timer: null,
        onPageShow: null,

        init() {
            this.onPageShow = () => this.reset();
            window.addEventListener('pageshow', this.onPageShow);
        },

        submit(event) {
            if (this.busy) {
                event.preventDefault();

                return;
            }

            // Another listener (the minutes editor, the upload zone) has already cancelled the submit
            if (event.defaultPrevented) {
                return;
            }

            const form = event.target;

            if (!form.checkValidity()) {
                return;
            }

            this.busy = true;

            const label = form.dataset.busyLabel ?? 'Guardando';
            const submitter = event.submitter ?? null;
            const buttons = Array.from(form.elements).filter((element) =>
                element.matches('button[type="submit"]'),
            );

            buttons.forEach((button) => {
                originals.set(button, {
                    texts: textNodes(button).map((node) => [node, node.nodeValue]),
                    disabled: button.disabled,
                });
                button.setAttribute('aria-busy', 'true');

                if (submitter === null || button === submitter) {
                    setLabel(button, button.dataset.busyLabel ?? label);
                }
            });

            // Disabled once the browser has read the submitter, so a named button still reaches the server.
            // By then every listener has run: if one of them cancelled the submit, the form is free again.
            setTimeout(() => {
                if (event.defaultPrevented) {
                    this.reset();

                    return;
                }

                buttons.forEach((button) => {
                    button.disabled = true;
                });
            }, 0);

            if ('download' in form.dataset) {
                this.timer = setTimeout(() => this.reset(), DOWNLOAD_RELEASE);
            }
        },

        reset() {
            clearTimeout(this.timer);
            this.busy = false;

            originals.forEach(({ texts, disabled }, button) => {
                texts.forEach(([node, value]) => {
                    node.nodeValue = value;
                });
                button.disabled = disabled;
                button.removeAttribute('aria-busy');
            });
            originals.clear();
        },

        destroy() {
            clearTimeout(this.timer);
            window.removeEventListener('pageshow', this.onPageShow);
        },
    };
}
