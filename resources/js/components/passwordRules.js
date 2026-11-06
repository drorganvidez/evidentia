/**
 * Live checklist of a new password (the same rule as App\Support\StrongPassword): length, uppercase, lowercase,
 * number, symbol and the repetition. The submit button of the form stays disabled until every rule holds; with
 * data-optional an empty password and repetition also count as valid (a field that may stay as it is).
 *
 * Blade: <x-ui.password-rules for="field-password" confirm="field-password-confirmation" />
 */
export default function passwordRules() {
    let password = null;
    let confirmation = null;
    let submit = null;

    return {
        value: '',
        repeated: '',

        init() {
            const root = this.$root;
            password = document.getElementById(root.dataset.for);
            confirmation = document.getElementById(root.dataset.confirm);
            submit = root.closest('form')?.querySelector('button[type="submit"]');

            this.onInput = () => this.read();
            password?.addEventListener('input', this.onInput);
            confirmation?.addEventListener('input', this.onInput);
            this.read();
        },

        destroy() {
            password?.removeEventListener('input', this.onInput);
            confirmation?.removeEventListener('input', this.onInput);
        },

        read() {
            this.value = password?.value ?? '';
            this.repeated = confirmation?.value ?? '';

            if (submit) {
                submit.disabled = !this.valid;
                submit.setAttribute('aria-disabled', this.valid ? 'false' : 'true');
            }
        },

        get min() {
            return Number(this.$root.dataset.min || 8);
        },

        get longEnough() {
            return this.value.length >= this.min;
        },

        get hasUpper() {
            return /\p{Lu}/u.test(this.value);
        },

        get hasLower() {
            return /\p{Ll}/u.test(this.value);
        },

        get hasNumber() {
            return /\p{N}/u.test(this.value);
        },

        get hasSymbol() {
            return /[^\p{L}\p{N}\s]/u.test(this.value);
        },

        get matches() {
            return this.value !== '' && this.value === this.repeated;
        },

        get valid() {
            if (
                this.$root.hasAttribute('data-optional') &&
                this.value === '' &&
                this.repeated === ''
            ) {
                return true;
            }

            return (
                this.longEnough &&
                this.hasUpper &&
                this.hasLower &&
                this.hasNumber &&
                this.hasSymbol &&
                this.matches
            );
        },

        get summary() {
            return this.valid ? 'La contraseña cumple todos los requisitos' : '';
        },
    };
}
