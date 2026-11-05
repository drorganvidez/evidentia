/*
 * Applies the theme before the first paint so the page never flashes the wrong one.
 * Inlined in <head> with the request nonce by the base layout (not a module: modules are
 * deferred and would run after the body has been painted). Classic script, no import/export.
 * The storage key and the data-theme attribute must match components/theme.js and the
 * dark variant in resources/css/app.css.
 */
(function () {
    var KEY = 'evidentia.theme';
    var mode = 'system';

    try {
        var stored = localStorage.getItem(KEY);

        if (stored === 'light' || stored === 'dark') {
            mode = stored;
        }
    } catch (error) {
        // Storage unavailable: follow the system preference.
    }

    var dark =
        mode === 'dark' ||
        (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
})();
