/*
 * Applies an explicitly chosen theme before the first paint so the page never flashes the
 * wrong one. In system mode nothing is written: the CSS media query decides, also without JS.
 * Inlined in <head> with the request nonce by the base layout (not a module: modules are
 * deferred and would run after the body has been painted). Classic script, no import/export.
 * The storage key, the data-theme attribute and the colours must match resources/js/stores/theme.js
 * and the dark variant in resources/css/app.css.
 */
(function () {
    var KEY = 'evidentia.theme';
    var COLORS = { light: '#f3f7fe', dark: '#151922' };
    var mode = null;

    try {
        var stored = localStorage.getItem(KEY);

        if (stored === 'light' || stored === 'dark') {
            mode = stored;
        }
    } catch {
        // Storage unavailable: follow the system preference.
    }

    if (!mode) {
        return;
    }

    document.documentElement.setAttribute('data-theme', mode);

    var metas = document.querySelectorAll('meta[name="theme-color"]');

    for (var i = 0; i < metas.length; i++) {
        metas[i].setAttribute('content', COLORS[mode]);
    }
})();
