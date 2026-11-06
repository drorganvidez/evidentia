/**
 * Combobox with a listbox and chips over a native <select> (<x-ui.picker>). The select stays the source of
 * truth: the options are read from it in init and every change is written back to it, so the form posts the
 * chosen values with or without JS. Presets replace the selection after a confirmation.
 *
 * Blade: root x-data="picker" with data-presets, data-max and data-max-visible; x-ref="native" on the select,
 * x-ref="input" on the combobox, x-ref="cancel" on the button that cancels a preset.
 */
function normalize(text) {
    return text
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase()
        .trim();
}

function people(count) {
    return `${count} ${count === 1 ? 'persona' : 'personas'}`;
}

function selectedText(count) {
    return `${people(count)} ${count === 1 ? 'seleccionada' : 'seleccionadas'}`;
}

export default function picker() {
    return {
        options: [],
        query: '',
        open: false,
        activeIndex: -1,
        ready: false,
        multiple: true,
        max: null,
        maxVisible: 50,
        presets: [],
        presetIndex: '',
        confirmingPreset: false,
        status: '',
        keepClosed: false,

        init() {
            const native = this.$refs.native;
            const id = this.$refs.input.id;

            this.multiple = native.multiple;
            this.max = Number(this.$root.dataset.max) || null;
            this.maxVisible = Number(this.$root.dataset.maxVisible) || 50;
            this.presets = JSON.parse(this.$root.dataset.presets || '[]');
            this.options = Array.from(native.options)
                .filter((option) => option.value !== '')
                .map((option, index) => {
                    const label = option.text.trim();
                    const hint = option.dataset.hint ?? '';

                    return {
                        value: option.value,
                        label,
                        hint,
                        selected: option.selected,
                        active: false,
                        id: `${id}-option-${index}`,
                        removeLabel: `Quitar a ${label}`,
                        search: normalize(`${label} ${hint}`),
                    };
                });

            if (!this.multiple) {
                this.query = this.current()?.label ?? '';
            }

            this.ready = true;
        },

        /** The chosen option in single mode. */
        current() {
            return this.options.find((option) => option.selected) ?? null;
        },

        get matching() {
            const term = normalize(this.query);
            const current = this.multiple ? null : this.current();
            // In single mode the chosen label sitting in the box means "show everything"
            const needle = current && term === normalize(current.label) ? '' : term;

            return this.options.filter(
                (option) =>
                    (this.multiple ? !option.selected : true) &&
                    (needle === '' || option.search.includes(needle)),
            );
        },

        get filtered() {
            return this.matching.slice(0, this.maxVisible);
        },

        get truncated() {
            return this.matching.length > this.maxVisible;
        },

        get truncatedText() {
            return `Se muestran ${this.maxVisible} de ${this.matching.length} resultados, sigue escribiendo`;
        },

        get chosen() {
            return this.options.filter((option) => option.selected);
        },

        get activeId() {
            const option = this.filtered[this.activeIndex];

            return this.open && option ? option.id : null;
        },

        get presetWarning() {
            const preset = this.preset();

            if (!preset) {
                return '';
            }

            return `Sustituirá la selección actual (${people(this.chosen.length)}) por ${preset.label} (${preset.values.length})`;
        },

        preset() {
            return this.presetIndex === ''
                ? null
                : (this.presets[Number(this.presetIndex)] ?? null);
        },

        /** Focus from the user opens the list; the focus the module gives back does not. */
        show() {
            if (!this.keepClosed) {
                this.open = true;
            }
        },

        /** The focus event fires synchronously inside focus(), so the flag only covers that call. */
        focusInput() {
            this.keepClosed = true;
            this.$refs.input.focus();
            this.keepClosed = false;
        },

        hide() {
            this.open = false;
            this.setActive(-1);
        },

        onInput(event) {
            this.query = event.target.value;
            this.open = true;
            this.setActive(this.query.trim() !== '' && this.filtered.length ? 0 : -1);
        },

        setActive(index) {
            this.activeIndex = index;
            this.options.forEach((option) => {
                option.active = false;
            });

            const option = this.filtered[index];

            if (option) {
                option.active = true;
                this.$nextTick(() => {
                    document.getElementById(option.id)?.scrollIntoView({ block: 'nearest' });
                });
            }
        },

        activate(option) {
            const index = this.filtered.indexOf(option);

            if (index !== -1) {
                this.setActive(index);
            }
        },

        toggle(option) {
            if (this.multiple) {
                if (!option.selected && this.max && this.chosen.length >= this.max) {
                    this.status = `Solo puedes elegir ${people(this.max)}`;

                    return;
                }

                option.selected = !option.selected;
                this.query = '';
                this.sync();
                this.setActive(Math.min(this.activeIndex, this.filtered.length - 1));
                this.status = `${option.label} ${option.selected ? 'añadido' : 'quitado'}, ${selectedText(this.chosen.length)}`;
            } else {
                this.options.forEach((item) => {
                    item.selected = item === option;
                });
                this.query = option.label;
                this.sync();
                this.hide();
                this.status = `${option.label} seleccionado`;
            }

            this.focusInput();
        },

        remove(option) {
            option.selected = false;
            this.sync();
            this.status = `${option.label} quitado, ${selectedText(this.chosen.length)}`;
            this.focusInput();
        },

        /** Writes the selection back to the native select, which is what the form posts. */
        sync() {
            const native = this.$refs.native;
            const any = this.options.some((option) => option.selected);

            Array.from(native.options).forEach((element) => {
                const option = this.options.find((item) => item.value === element.value);

                element.selected = option ? option.selected : element.value === '' && !any;
            });
            native.dispatchEvent(new Event('change', { bubbles: true }));
        },

        onKeydown(event) {
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.open = true;
                    this.setActive(Math.min(this.activeIndex + 1, this.filtered.length - 1));
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.open = true;
                    this.setActive(Math.max(this.activeIndex - 1, 0));
                    break;
                case 'Home':
                    if (this.open) {
                        event.preventDefault();
                        this.setActive(0);
                    }
                    break;
                case 'End':
                    if (this.open) {
                        event.preventDefault();
                        this.setActive(this.filtered.length - 1);
                    }
                    break;
                case 'Enter':
                    if (this.open) {
                        event.preventDefault();

                        if (this.filtered[this.activeIndex]) {
                            this.toggle(this.filtered[this.activeIndex]);
                        }
                    }
                    break;
                case 'Escape':
                    if (this.open) {
                        event.preventDefault();
                        this.hide();
                    } else if (!this.multiple) {
                        this.query = this.current()?.label ?? '';
                    }
                    break;
                case 'Backspace':
                    if (this.multiple && this.query === '' && this.chosen.length) {
                        this.remove(this.chosen[this.chosen.length - 1]);
                    }
                    break;
                case 'Tab':
                    this.hide();
                    break;
                default:
                    break;
            }
        },

        onFocusOut(event) {
            if (event.relatedTarget && this.$root.contains(event.relatedTarget)) {
                return;
            }

            this.hide();

            if (!this.multiple) {
                this.query = this.current()?.label ?? '';
            }
        },

        askPreset() {
            if (!this.preset()) {
                return;
            }

            this.confirmingPreset = true;
            this.$nextTick(() => this.$refs.cancel?.focus());
        },

        applyPreset() {
            const preset = this.preset();

            if (!preset) {
                return;
            }

            const values = preset.values.map(String);

            this.options.forEach((option) => {
                option.selected = values.includes(option.value);
            });
            this.sync();
            this.confirmingPreset = false;
            this.status = `Lista cargada, ${selectedText(this.chosen.length)}`;
            this.focusInput();
        },

        cancelPreset() {
            this.confirmingPreset = false;
            this.focusInput();
        },
    };
}
