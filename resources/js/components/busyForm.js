/**
 * Busy state of <x-ui.form>: blocks the double submit, relabels the pressed submit control and frees
 * itself when the page comes back from the history cache or, for downloads, after a few seconds.
 * A submit that another listener cancels (a validation of its own) leaves the form free.
 *
 * The submit event only fires once the browser lets the form go (always with novalidate), so every
 * submit that reaches the handler marks the form busy. Buttons and input type=submit or image count,
 * also those outside the form that point to it with form="id".
 *
 * Blade: <form x-data="busyForm" data-busy-label="Guardando" x-on:submit="submit"
 * x-bind:aria-busy="busy ? 'true' : 'false'"> with data-download when the response is a file.
 * A submit control may carry its own data-busy-label. Every access to window and the DOM lives here.
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

/** Submit controls of a form: buttons (submit is their default type) and inputs of type submit or image. */
function submitControls(form) {
    return Array.from(form.elements).filter(
        (element) =>
            element.type === 'submit' || (element.tagName === 'INPUT' && element.type === 'image'),
    );
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
            const label = form.dataset.busyLabel ?? 'Guardando';
            const submitter = event.submitter ?? null;
            const controls = submitControls(form);
            const relabels = (control) => submitter === null || control === submitter;

            this.busy = true;

            controls.forEach((control) => {
                originals.set(control, {
                    texts: textNodes(control).map((node) => [node, node.nodeValue]),
                    value: control.tagName === 'INPUT' ? control.value : null,
                    disabled: control.disabled,
                });
                control.setAttribute('aria-busy', 'true');

                // The text of a button never travels; the value of an input waits for the timer below
                if (control.tagName === 'BUTTON' && relabels(control)) {
                    setLabel(control, control.dataset.busyLabel ?? label);
                }
            });

            // Once the browser has read the submitter and its value, so a named control still reaches the
            // server with what it says. By then every listener has run: if one of them cancelled the
            // submit, the form is free again.
            setTimeout(() => {
                if (event.defaultPrevented) {
                    this.reset();

                    return;
                }

                controls.forEach((control) => {
                    control.disabled = true;

                    if (
                        control.tagName === 'INPUT' &&
                        control.type === 'submit' &&
                        relabels(control)
                    ) {
                        control.value = control.dataset.busyLabel ?? label;
                    }
                });
            }, 0);

            if ('download' in form.dataset) {
                this.timer = setTimeout(() => this.reset(), DOWNLOAD_RELEASE);
            }
        },

        reset() {
            clearTimeout(this.timer);
            this.busy = false;

            originals.forEach(({ texts, value, disabled }, control) => {
                texts.forEach(([node, text]) => {
                    node.nodeValue = text;
                });

                if (value !== null) {
                    control.value = value;
                }

                control.disabled = disabled;
                control.removeAttribute('aria-busy');
            });
            originals.clear();
        },

        destroy() {
            clearTimeout(this.timer);
            window.removeEventListener('pageshow', this.onPageShow);
        },
    };
}
