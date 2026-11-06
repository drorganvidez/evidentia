import { announce } from '../alpine/announce';

/**
 * Character counter of a textarea with maxlength (<x-ui.field type="textarea" :maxlength="1000">).
 *
 * Blade: the field root carries x-data="charCount", the textarea x-on:input="update" and the counter
 * <span x-text="text">. The limit is read from the textarea itself. The 90 % mark and the limit are
 * announced once each through the notices store (or a live region of its own), no aria-live here.
 */
export default function charCount() {
    return {
        length: 0,
        max: 0,
        warned: false,
        full: false,

        init() {
            const area = this.$root.querySelector('textarea');

            this.max = Number(area?.getAttribute('maxlength')) || 0;
            this.length = area ? area.value.length : 0;
        },

        update(event) {
            this.length = event.target.value.length;

            if (!this.max) {
                return;
            }

            if (this.length >= this.max) {
                if (!this.full) {
                    this.full = true;
                    announce(
                        this.$store.notices,
                        `Has llegado al máximo de ${this.max} caracteres`,
                        'status',
                    );
                }

                return;
            }

            this.full = false;

            if (this.length >= this.max * 0.9) {
                if (!this.warned) {
                    this.warned = true;
                    announce(
                        this.$store.notices,
                        `Quedan ${this.max - this.length} caracteres`,
                        'status',
                    );
                }

                return;
            }

            this.warned = false;
        },

        get text() {
            return `${this.length} de ${this.max} caracteres`;
        },

        get over() {
            return this.max > 0 && this.length > this.max;
        },
    };
}
