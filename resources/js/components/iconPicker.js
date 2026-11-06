/**
 * Icon picker (<x-ui.icon-picker>): filters the radios by name or Spanish alias, mirrors the chosen icon in
 * the preview and announces how many icons match once the typing pauses.
 *
 * Blade: <fieldset x-data="iconPicker" data-aliases='{"euro":"euro, dinero, finanzas"}'> with x-model="query"
 * and x-on:input.debounce="announceCount" on the search, x-show="matches('euro')" on every label,
 * x-on:change="choose('euro')" on every radio and x-ref="preview" on the preview box.
 */
function normalize(text) {
    return text
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase()
        .trim();
}

export default function iconPicker() {
    return {
        value: '',
        query: '',
        count: null,
        aliases: {},

        init() {
            this.aliases = JSON.parse(this.$root.dataset.aliases || '{}');
            this.value = this.$root.querySelector('input[type="radio"]:checked')?.value ?? '';
        },

        matches(name) {
            const needle = normalize(this.query);

            if (needle === '') {
                return true;
            }

            return normalize(`${name} ${this.aliases[name] ?? ''}`).includes(needle);
        },

        /** The preview copies the SVG of the chosen radio: the value posted is only the name. */
        choose(name) {
            this.value = name;

            const svg = this.$root
                .querySelector(`input[value="${name}"]`)
                ?.closest('label')
                ?.querySelector('svg');

            if (svg && this.$refs.preview) {
                this.$refs.preview.replaceChildren(svg.cloneNode(true));
            }
        },

        announceCount() {
            this.count = Array.from(this.$root.querySelectorAll('input[type="radio"]')).filter(
                (radio) => this.matches(radio.value),
            ).length;
        },

        get countText() {
            if (this.count === null) {
                return '';
            }

            return `${this.count} ${this.count === 1 ? 'icono' : 'iconos'}`;
        },
    };
}
