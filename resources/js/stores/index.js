// Every module in this directory is an Alpine store registered with Alpine.store() under its
// file name: resources/js/stores/theme.js becomes $store.theme. Nothing else edits this file.
const modules = import.meta.glob(['./*.js', '!./index.js'], { eager: true, import: 'default' });

export default Object.fromEntries(
    Object.entries(modules).map(([path, store]) => [path.slice(2, -3), store]),
);
