/**
 * Show / hide toggle of a password field (<x-ui.field type="password">).
 *
 * Blade: the field root carries x-data="passwordField", the input x-bind:type="type" and the button
 * x-on:click="toggle" x-bind:aria-pressed="visible ? 'true' : 'false'". The button keeps its name
 * «Mostrar la contraseña»: a toggle button says its state with aria-pressed, not with a new name.
 */
export default function passwordField() {
    return {
        visible: false,

        get type() {
            return this.visible ? 'text' : 'password';
        },

        toggle() {
            this.visible = !this.visible;
        },
    };
}
