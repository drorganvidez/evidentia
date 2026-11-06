/**
 * Thin progress bar at the top of the page while the next page is on its way, so a slow answer of the server
 * never looks like a click that did nothing (visibility of system status). It appears only when the wait passes
 * 200 ms, so fast pages do not flicker, and it starts on a click on a link of the app or on a form submission
 * that no other listener cancelled. Coming back through the history (pageshow) hides it again.
 *
 * Blade (layout base): <div x-data="pageProgress" x-bind:data-active="active ? 'true' : null" aria-hidden="true">
 */
const DELAY = 200;

/** A plain left click on a link that loads another document of this origin in this tab. */
function isNavigation(event) {
    if (event.defaultPrevented || event.button !== 0) {
        return false;
    }

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return false;
    }

    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

    if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) {
        return false;
    }

    const url = new URL(link.href, window.location.href);

    if (url.origin !== window.location.origin || !['http:', 'https:'].includes(url.protocol)) {
        return false;
    }

    // Same document with another fragment: the browser only scrolls
    return !(
        url.pathname === window.location.pathname &&
        url.search === window.location.search &&
        url.hash
    );
}

export default function pageProgress() {
    let timer = null;

    const start = (component) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            component.active = true;
        }, DELAY);
    };

    return {
        active: false,

        init() {
            this.onClick = (event) => {
                if (isNavigation(event)) {
                    start(this);
                }
            };

            // After the other listeners: a form whose submission another module cancels shows nothing
            this.onSubmit = (event) => {
                setTimeout(() => {
                    const form = event.target;

                    if (
                        !event.defaultPrevented &&
                        !(form instanceof HTMLFormElement && form.hasAttribute('data-download'))
                    ) {
                        start(this);
                    }
                }, 0);
            };

            this.onShow = () => {
                clearTimeout(timer);
                this.active = false;
            };

            document.addEventListener('click', this.onClick);
            document.addEventListener('submit', this.onSubmit);
            window.addEventListener('pageshow', this.onShow);
        },

        destroy() {
            clearTimeout(timer);
            document.removeEventListener('click', this.onClick);
            document.removeEventListener('submit', this.onSubmit);
            window.removeEventListener('pageshow', this.onShow);
        },
    };
}
