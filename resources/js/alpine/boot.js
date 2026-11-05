import Alpine from '@alpinejs/csp';

/**
 * Single Alpine start-up (CSP build: no eval, every behaviour lives in a registered component).
 * Order required by Alpine: import, register plugins and components, then Alpine.start() once.
 *
 * @param {{ plugins?: Function[], components?: Record<string, Function>, stores?: Record<string, object> }} options
 */
export function bootAlpine({ plugins = [], components = {}, stores = {} } = {}) {
    plugins.forEach((plugin) => Alpine.plugin(plugin));

    Object.entries(stores).forEach(([name, store]) => Alpine.store(name, store));
    Object.entries(components).forEach(([name, factory]) => Alpine.data(name, factory));

    window.Alpine = Alpine;

    Alpine.start();

    return Alpine;
}

export { Alpine };
