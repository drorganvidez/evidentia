/**
 * Clipboard behaviour of <x-ui.button copy="...">: copies the text held in data-copy of the root element.
 *
 * Blade: <button x-data="clipboard" data-copy="https://..." x-on:click="copy"> with the two icons
 * swapped by x-show="!copied" and x-show="copied". Every access to navigator and document lives here.
 *
 * The outcome is announced through the notices store when the page registers it ($store.notices);
 * without it the module keeps a visually hidden status region of its own, so the pages that load
 * fewer modules still announce the copy to assistive technology.
 */
const COPIED_FOR = 2000;
const CLEAR_AFTER = 5000;
const COPIED_TEXT = 'Copiado al portapapeles';
const FAILED_TEXT = 'No se ha podido copiar';

let region = null;
let clearTimer = null;

function fallbackAnnounce(text) {
    if (!region) {
        region = document.createElement('p');
        region.setAttribute('role', 'status');
        region.className = 'sr-only';
        document.body.append(region);
    }

    region.textContent = text;
    clearTimeout(clearTimer);
    clearTimer = setTimeout(() => {
        region.textContent = '';
    }, CLEAR_AFTER);
}

async function writeText(text) {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);

        return;
    }

    // Insecure contexts and older browsers: a temporary textarea and the copy command.
    const area = document.createElement('textarea');
    area.value = text;
    area.setAttribute('readonly', '');
    area.setAttribute('aria-hidden', 'true');
    area.className = 'sr-only';
    document.body.append(area);
    area.select();

    try {
        if (!document.execCommand('copy')) {
            throw new Error('The copy command was refused');
        }
    } finally {
        area.remove();
    }
}

export default function clipboard() {
    return {
        copied: false,
        timer: null,

        async copy() {
            const text = this.$root.dataset.copy ?? '';

            try {
                await writeText(text);
            } catch {
                this.notify('rejected', FAILED_TEXT);

                return;
            }

            this.copied = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.copied = false;
            }, COPIED_FOR);
            this.notify('accepted', COPIED_TEXT);
        },

        /** Through the notices store when it exists, otherwise through the module's own status region. */
        notify(tone, text) {
            const notices = this.$store.notices;

            if (!notices) {
                fallbackAnnounce(text);

                return;
            }

            if (tone === 'rejected') {
                notices.push({ tone, text });
            } else {
                notices.announce(text);
            }
        },

        destroy() {
            clearTimeout(this.timer);
        },
    };
}
