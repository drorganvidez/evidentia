{{--
    People picker: a combobox with a listbox and chips over a native <select multiple>, which stays the source of truth
    (without JS the form still sends name[]). Presets replace the selection after a confirmation.
    <x-ui.picker name="users[]" label="Asistentes" :options="$people" :selected="$chosen" :presets="$lists" />
    <x-ui.picker name="student" label="Alumno" :options="$students" :max="1" placeholder="Buscar al alumno" />
    options is a list of {value, label, hint}; presets a list of {label, values}. max 1 keeps one choice in the box, no chips.
    selected rules the first load only: once a failed validation brings the input back, old() decides and a missing key
    means nobody chosen; inside a GET x-ui.form old() is never read.
--}}
@props([
    'name' => '',
    'label' => '',
    'options' => [],
    'selected' => [],
    'presets' => [],
    'placeholder' => 'Buscar por nombre o apellidos',
    'hint' => null,
    'error' => null,
    'max' => null,
    'maxVisible' => 50,
    'id' => null,
])
@aware(['method' => null])
@php
    throw_if(blank($name), InvalidArgumentException::class, 'The picker component needs a name.');
    throw_if(blank($label), InvalidArgumentException::class, 'The picker component needs a label.');

    $single = (int) $max === 1;
    $base = str_ends_with($name, '[]') ? substr($name, 0, -2) : $name;
    $inputName = $single ? $base : $base . '[]';
    $errorKey = ui_error_key($base);
    $id ??= ui_field_id($base);

    // Never assume $errors: the components also render through Blade::render
    $bag = $errors ?? null;
    if ($error === null && $bag instanceof \Illuminate\Support\ViewErrorBag) {
        $error = $bag->first($errorKey) ?: ($bag->first($errorKey . '.*') ?: null);
    }

    // After a failed validation the input that came back decides: with nobody chosen the key is not even sent
    if (strtolower((string) $method) !== 'get' && request()->hasSession() && request()->session()->hasOldInput()) {
        $selected = old($errorKey, []);
    }
    $selected = array_map('strval', array_values((array) $selected));

    $items = [];
    foreach ($options as $option) {
        $items[] = [
            'value' => (string) ($option['value'] ?? ''),
            'label' => (string) ($option['label'] ?? ''),
            'hint' => isset($option['hint']) ? (string) $option['hint'] : null,
        ];
    }
    $lists = [];
    foreach ($presets as $preset) {
        $lists[] = [
            'label' => (string) ($preset['label'] ?? ''),
            'values' => array_map('strval', array_values((array) ($preset['values'] ?? []))),
        ];
    }
    $describedBy = implode(
        ' ',
        array_filter([$error !== null ? $id . '-error' : null, $hint !== null ? $id . '-hint' : null]),
    );
@endphp
<div x-data="picker" data-presets="{{ json_encode($lists, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) }}"
    data-max="{{ $max ?? '' }}" data-max-visible="{{ (int) $maxVisible }}" x-on:click.outside="hide"
    x-on:focusout="onFocusOut" {{ $attributes->class(['grid grid-cols-[minmax(0,1fr)] gap-1.5']) }}>
    <label for="{{ $id }}" id="{{ $id }}-label"
        class="text-sm font-semibold">{{ $label }}</label>
    <select name="{{ $inputName }}" id="{{ $id }}-native" x-ref="native" tabindex="-1"
        aria-labelledby="{{ $id }}-label" x-bind:aria-hidden="ready ? 'true' : null" class="sr-only"
        @unless ($single) multiple @endunless>
        @if ($single)
            <option value="" @selected($selected === [])>Sin selección</option>
        @endif
        @foreach ($items as $item)
            <option value="{{ $item['value'] }}" @selected(in_array($item['value'], $selected, true))
                @if ($item['hint'] !== null) data-hint="{{ $item['hint'] }}" @endif>{{ $item['label'] }}</option>
        @endforeach
    </select>
    <div class="relative">
        <div
            class="flex min-h-10.5 flex-wrap items-center gap-1.5 rounded-md border border-field-edge bg-field px-3 py-1.5 transition focus-within:border-aurora-500 focus-within:bg-glass-2 focus-within:ring-4 focus-within:ring-aurora-500/18 has-[[aria-invalid=true]]:border-rejected has-[[aria-invalid=true]]:focus-within:ring-rejected/18 pointer-coarse:min-h-11">
            @unless ($single)
                <ul x-show="chosen.length" x-cloak class="flex flex-wrap gap-1.5" aria-label="Seleccionados">
                    <template x-for="option in chosen" x-bind:key="option.value">
                        <li>
                            <x-ui.badge variant="chip">
                                <span x-text="option.label"></span>
                                <x-ui.button icon-only icon="x" variant="ghost" size="sm" label="Quitar"
                                    x-on:click="remove(option)" x-bind:aria-label="option.removeLabel"
                                    x-bind:title="option.removeLabel" />
                            </x-ui.badge>
                        </li>
                    </template>
                </ul>
            @endunless
            <input id="{{ $id }}" type="text" role="combobox" aria-autocomplete="list"
                aria-controls="{{ $id }}-list" aria-expanded="false"
                x-bind:aria-expanded="open ? 'true' : 'false'" x-bind:aria-activedescendant="activeId" x-ref="input"
                x-model="query" x-on:focus="show" x-on:input="onInput" x-on:keydown="onKeydown" inputmode="search"
                autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="{{ $placeholder }}"
                class="min-w-32 flex-1 border-0 bg-transparent text-control text-ink outline-none placeholder:text-ink-muted"
                @if ($error !== null) aria-invalid="true" @endif
                @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
        </div>
        <div x-show="open" x-cloak class="glass absolute inset-x-0 top-full z-10 mt-1 rounded-lg p-1.5">
            <ul id="{{ $id }}-list" role="listbox" aria-labelledby="{{ $id }}-label"
                @unless ($single) aria-multiselectable="true" @endunless
                class="max-h-64 overflow-y-auto">
                <template x-for="option in filtered" x-bind:key="option.value">
                    <li role="option" x-bind:id="option.id"
                        x-bind:aria-selected="option.selected ? 'true' : 'false'"
                        x-bind:data-active="option.active ? '' : null" x-on:mousedown.prevent="toggle(option)"
                        x-on:mouseenter="activate(option)"
                        class="flex min-h-11 cursor-pointer items-center justify-between gap-3 rounded-sm px-3 py-2 text-md aria-selected:font-semibold data-[active]:bg-brand-soft data-[active]:text-brand-soft-ink">
                        <span class="grid">
                            <span x-text="option.label"></span>
                            <span class="text-xs text-ink-muted" x-show="option.hint" x-text="option.hint"></span>
                        </span>
                        <x-lucide-check x-show="option.selected" class="size-4 shrink-0" aria-hidden="true" />
                    </li>
                </template>
            </ul>
            <p x-show="!filtered.length" class="px-3 py-2 text-sm text-ink-muted">Sin resultados</p>
            <p x-show="truncated" class="px-3 py-2 text-xs text-ink-muted" x-text="truncatedText"></p>
        </div>
    </div>
    @if ($lists !== [])
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <label for="{{ $id }}-preset" class="text-ink-muted">Lista predeterminada</label>
            <select id="{{ $id }}-preset" x-model="presetIndex"
                class="h-9 min-w-0 rounded-md border border-field-edge bg-field px-3 text-control text-ink">
                <option value="">Elige una lista</option>
                @foreach ($lists as $index => $list)
                    <option value="{{ $index }}">{{ $list['label'] }} ({{ count($list['values']) }})</option>
                @endforeach
            </select>
            <x-ui.button size="sm" x-on:click="askPreset" x-bind:disabled="presetIndex === ''"
                disabled>Cargar</x-ui.button>
        </div>
        <div x-show="confirmingPreset" x-cloak role="group" aria-label="Confirmar la carga"
            class="glass-2 flex flex-wrap items-center gap-3 rounded-lg p-3 text-sm">
            <span x-text="presetWarning"></span>
            <x-ui.button size="sm" variant="primary" x-on:click="applyPreset">Sustituir</x-ui.button>
            <x-ui.button size="sm" x-on:click="cancelPreset" x-ref="cancel">Cancelar</x-ui.button>
        </div>
    @endif
    <p role="status" class="sr-only" x-text="status"></p>
    @if ($hint !== null)
        <p id="{{ $id }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    @if ($error !== null)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">
            <x-lucide-circle-alert class="size-4" aria-hidden="true" />{{ $error }}
        </p>
    @endif
</div>
