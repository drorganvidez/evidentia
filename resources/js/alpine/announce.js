/**
 * Announces a text to assistive technology: through the notices store when the page registers it
 * ($store.notices.announce), otherwise through a visually hidden live region of its own, so the
 * pages that load fewer modules still announce. Shared by the form modules; not an Alpine component.
 */
const CLEAR_AFTER = 5000;
const regions = {};
const timers = {};

export function announce(store, text, kind = 'status') {
    if (store && typeof store.announce === 'function') {
        store.announce(text, kind);

        return;
    }

    if (!regions[kind]) {
        const region = document.createElement('p');

        region.setAttribute('role', kind);
        region.className = 'sr-only';
        document.body.append(region);
        regions[kind] = region;
    }

    regions[kind].textContent = text;
    clearTimeout(timers[kind]);
    timers[kind] = setTimeout(() => {
        regions[kind].textContent = '';
    }, CLEAR_AFTER);
}
