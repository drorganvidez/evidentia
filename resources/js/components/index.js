// Every module in this directory is an Alpine component registered with Alpine.data() under its
// file name: resources/js/components/dialog.js becomes x-data="dialog". Nothing else edits this file.
const modules = import.meta.glob(['./*.js', '!./index.js'], { eager: true, import: 'default' });

export default Object.fromEntries(
    Object.entries(modules).map(([path, factory]) => [path.slice(2, -3), factory]),
);
