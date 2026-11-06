/**
 * State of a group of checkboxes (<x-ui.checkbox-group>), so a dependent block can follow the choice.
 *
 * Blade: <fieldset x-data="checkboxGroup" data-name="roles[]"> with x-on:change="sync" on every box and
 * x-show="hasAny('COORDINATOR', 'SECRETARY')" on the dependent block. The selection is read from the
 * ticked inputs, so the server-rendered state is the initial one.
 */
export default function checkboxGroup() {
    return {
        selected: [],

        init() {
            this.sync();
        },

        sync() {
            const name = this.$root.dataset.name;
            const selector = name
                ? `input[type="checkbox"][name="${name}"]`
                : 'input[type="checkbox"]';

            this.selected = Array.from(this.$root.querySelectorAll(selector))
                .filter((box) => box.checked)
                .map((box) => box.value);
        },

        has(value) {
            return this.selected.includes(value);
        },

        hasAny(...values) {
            return values.some((value) => this.selected.includes(value));
        },

        hasNone() {
            return this.selected.length === 0;
        },
    };
}
