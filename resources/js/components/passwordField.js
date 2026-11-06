/**
 * Show / hide toggle of a password field (<x-ui.field type="password">).
 *
 * Blade: the field root carries x-data="passwordField", the input x-bind:type="type" and the button
 * x-on:click="toggle" x-bind:aria-pressed="visible ? 'true' : 'false'" x-bind:aria-label="label".
 */
export default function passwordField() {
    return {
        visible: false,

        get type() {
            return this.visible ? 'text' : 'password';
        },

        get label() {
            return this.visible ? 'Ocultar la contraseña' : 'Mostrar la contraseña';
        },

        toggle() {
            this.visible = !this.visible;
        },
    };
}
