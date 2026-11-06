{{--
    Labelled form control: input, select or textarea with hint, error, icon, prefix or suffix and the server values.
    <x-ui.field name="title" label="Título" required hint="Mínimo 5 caracteres" />
    <x-ui.field name="hours" label="Horas" type="number" suffix="h" inputmode="decimal" min="0" step="0.25" />
    <x-ui.field name="committee" label="Comité" type="select" :options="$committees" placeholder="Elige un comité" />
    <x-ui.field name="description" label="Descripción" type="textarea" :rows="4" :maxlength="1000" />
    <x-ui.field name="password" label="Contraseña" type="password" size="lg" autocomplete="current-password" />
    The consumer class goes to the root (layout); every other attribute (min, max, step, autocomplete, inputmode,
    pattern, x-model) lands on the control. old() wins over value; the error comes from $errors or the error prop.
    value takes a scalar, a date (Y-m-d, H:i or Y-m-d\TH:i for those types, d/m/Y H:i otherwise), a backed enum or a
    Stringable; any other object fails loudly.
    A select is a combobox with a search box (selectField, APG editable combobox with list autocomplete) over the native
    <select>, which keeps the id, the name, the attributes and the options, posts the value and is the control without JS.
    A click opens the list with the chosen option marked and the box emptied; typing filters ignoring case and accents;
    arrows, Home and End move, Enter chooses, Escape closes and puts the chosen label back, Tab leaves. Every choice
    writes the select and fires input and change on it, so x-model and the forms that follow it keep working. The list
    is a popover of the top layer pinned to the box, above it when there is no room below, so no card clips it.
    options is value => label or a list of {value, label, disabled}; placeholder is the empty choice and never listed.
    :submit="false" leaves the name out of the select, for a choice that only drives the page.
--}}
@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'icon' => null,
    'size' => 'md',
    'prefix' => null,
    'suffix' => null,
    'rows' => 3,
    'maxlength' => null,
    'hideLabel' => false,
    'toggle' => true,
    'submit' => true,
    'id' => null,
])
@php
    throw_if(blank($name), InvalidArgumentException::class, 'The field component needs a name.');
    throw_if(blank($label), InvalidArgumentException::class, 'The field component needs a label.');

    $types = [
        'text',
        'email',
        'password',
        'number',
        'date',
        'time',
        'datetime-local',
        'search',
        'url',
        'tel',
        'textarea',
        'select',
    ];
    $type = in_array($type, $types, true) ? $type : 'text';
    $id ??= ui_field_id($name);
    $errorKey = ui_error_key($name);
    // Never assume $errors: the components also render through Blade::render
    $bag = $errors ?? null;
    $error ??= $bag instanceof \Illuminate\Support\ViewErrorBag ? ($bag->first($errorKey) ?: null) : null;
    // old() brings strings, or arrays for a name[] field; the prop may also bring a date, an enum or a Stringable
    $current = old($errorKey) ?? $value;
    if ($current instanceof DateTimeInterface) {
        $current = $current->format(
            match ($type) {
                'date' => 'Y-m-d',
                'time' => 'H:i',
                'datetime-local' => 'Y-m-d\TH:i',
                default => 'd/m/Y H:i',
            },
        );
    } elseif ($current instanceof BackedEnum) {
        $current = $current->value;
    } elseif ($current instanceof Stringable) {
        $current = (string) $current;
    }
    throw_if(
        is_object($current),
        InvalidArgumentException::class,
        'The field value must be a scalar, a date, a backed enum or a Stringable.',
    );
    $current = is_scalar($current) ? (string) $current : '';

    $isTextarea = $type === 'textarea';
    $isSelect = $type === 'select';
    $hasToggle = $type === 'password' && $toggle;
    $hasCount = $isTextarea && $maxlength !== null;

    $describedBy = implode(
        ' ',
        array_filter([
            $error !== null ? $id . '-error' : null,
            $hint !== null ? $id . '-hint' : null,
            $hasCount ? $id . '-count' : null,
        ]),
    );

    // Options of a select: value => label, or a list of {value, label, disabled}
    $items = [];
    foreach ($options as $key => $option) {
        $items[] = is_array($option)
            ? [
                'value' => (string) ($option['value'] ?? ''),
                'label' => (string) ($option['label'] ?? ($option['value'] ?? '')),
                'disabled' => (bool) ($option['disabled'] ?? false),
            ]
            : ['value' => (string) $key, 'label' => (string) $option, 'disabled' => false];
    }

    $rowsClass = match ((int) $rows) {
        1 => '[--rows:1]',
        2 => '[--rows:2]',
        4 => '[--rows:4]',
        5 => '[--rows:5]',
        6 => '[--rows:6]',
        7 => '[--rows:7]',
        8 => '[--rows:8]',
        9 => '[--rows:9]',
        10 => '[--rows:10]',
        default => '[--rows:3]',
    };
    $heightClass = $isTextarea
        ? 'items-start py-2'
        : match ($size) {
            'lg' => 'h-12 items-center pointer-coarse:min-h-11',
            default => 'h-10.5 items-center pointer-coarse:min-h-11',
        };
    $controlClasses = [
        'w-0 min-w-0 flex-1 border-0 bg-transparent text-control text-ink outline-none placeholder:text-ink-muted disabled:cursor-not-allowed disabled:text-ink-muted',
        // the control fills the height of its box, 44 px under a coarse pointer, so the whole box is the target
        'self-stretch' => !$isTextarea,
        'cursor-pointer appearance-none pr-8' => $isSelect,
        'resize-none field-sizing-content min-h-[calc(var(--rows)*1.5rem)] max-h-[50dvh]' => $isTextarea,
        $rowsClass => $isTextarea,
    ];
    $control = $attributes->except('class');
    $root = $attributes->only('class')->class(['grid grid-cols-[minmax(0,1fr)] gap-1.5']);
    // A select labels its listbox too and points the label to the combobox once it is there
    $labelAttributes = new \Illuminate\View\ComponentAttributeBag(
        array_merge(['for' => $id], $isSelect ? ['id' => $id . '-label', 'x-bind:for' => 'labelFor'] : [], [
            'class' => $hideLabel ? 'sr-only' : 'text-sm font-semibold',
        ]),
    );
    if ($isSelect) {
        $root = $root->merge([
            'x-data' => 'selectField',
            'x-on:focusout' => 'onFocusOut',
            'x-on:click.outside' => 'dismiss',
            'x-on:scroll.window.capture' => 'place',
            'x-on:resize.window' => 'place',
        ]);
    } elseif ($hasToggle) {
        $root = $root->merge(['x-data' => 'passwordField']);
    } elseif ($hasCount) {
        $root = $root->merge(['x-data' => 'charCount']);
    }
@endphp
<div {{ $root }}>
    <label {{ $labelAttributes }}>{{ $label }}
        @if ($required)
            <span aria-hidden="true" class="text-rejected-ink after:content-['*']"></span>
        @endif
    </label>
    <div @if ($isSelect) x-ref="box" @endif
        class="{{ Arr::toCssClasses([
            'relative flex gap-2 rounded-md border border-field-edge bg-field pl-4 pr-3 transition focus-within:border-aurora-500 focus-within:bg-glass-2 focus-within:ring-4 focus-within:ring-aurora-500/18 has-[[aria-invalid=true]]:border-rejected has-[[aria-invalid=true]]:focus-within:ring-rejected/18 has-[:is(input,select,textarea):disabled]:border-dashed has-[:is(input,select,textarea):disabled]:bg-rule/40',
            $heightClass,
        ]) }}">
        @if ($icon !== null)
            <x-ui.icon :name="$icon" class="size-4.5 text-ink-muted" />
        @endif
        @if ($prefix !== null)
            <span class="shrink-0 text-md text-ink-muted">{{ $prefix }}</span>
        @endif
        @if ($isSelect)
            {{-- Without JS the select is the control; with JS it stays, hidden, as the source of truth --}}
            <select id="{{ $id }}" @if ($submit) name="{{ $name }}" @endif
                {{ $control->class($controlClasses) }} x-ref="source" x-on:change="read" x-on:focus="focusEntry"
                x-bind:class="ready ? 'sr-only' : ''" x-bind:tabindex="ready ? '-1' : null"
                x-bind:aria-hidden="ready ? 'true' : null" @required($required) @disabled($disabled)
                @if ($required) aria-required="true" @endif
                @if ($error !== null) aria-invalid="true" @endif
                @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                @if ($placeholder !== null)
                    <option value="" disabled data-placeholder @selected($current === '')>{{ $placeholder }}
                    </option>
                @endif
                @foreach ($items as $item)
                    <option value="{{ $item['value'] }}" @selected($current !== '' && $current === $item['value']) @disabled($item['disabled'])>
                        {{ $item['label'] }}</option>
                @endforeach
            </select>
            <input id="{{ $id }}-combobox" type="text" role="combobox" x-ref="entry" x-cloak
                aria-autocomplete="list" aria-controls="{{ $id }}-list" aria-expanded="false"
                x-bind:aria-expanded="open ? 'true' : 'false'" x-bind:aria-activedescendant="activeId" x-model="query"
                x-bind:placeholder="placeholderText" x-on:input="onInput" x-on:keydown="onKeydown"
                x-on:pointerdown="onPointerDown" x-on:focus="onFocus" x-on:click="toggle" autocomplete="off"
                autocapitalize="off" spellcheck="false"
                class="w-0 min-w-0 flex-1 cursor-pointer truncate border-0 bg-transparent pr-8 text-control text-ink outline-none placeholder:text-ink-muted focus:cursor-text disabled:cursor-not-allowed disabled:text-ink-muted"
                @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif
                @disabled($disabled) @if ($required) aria-required="true" @endif
                @if ($error !== null) aria-invalid="true" @endif
                @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
            <x-lucide-chevron-down class="pointer-events-none absolute right-3 size-4 text-ink-muted"
                aria-hidden="true" />
            {{-- A manual popover in the top layer, placed by the module: nothing around can clip or cover it --}}
            <div x-ref="popup" popover="manual" hidden
                class="glass fixed inset-auto z-50 m-0 rounded-lg p-1.5 text-ink">
                <ul id="{{ $id }}-list" role="listbox" aria-labelledby="{{ $id }}-label"
                    x-ref="list" class="relative max-h-64 overflow-y-auto overscroll-contain">
                    <template x-for="option in filtered" x-bind:key="option.id">
                        <li role="option" x-bind:id="option.id"
                            x-bind:aria-selected="option.selected ? 'true' : 'false'"
                            x-bind:aria-disabled="option.disabled ? 'true' : null"
                            x-bind:data-active="option.active ? '' : null" x-on:mousedown.prevent="choose(option)"
                            x-on:mouseenter="activate(option)"
                            class="flex min-h-11 cursor-pointer items-center justify-between gap-3 rounded-sm px-3 py-2 text-md aria-disabled:cursor-not-allowed aria-disabled:text-ink-muted aria-selected:font-semibold data-[active]:bg-brand-soft data-[active]:text-brand-soft-ink">
                            <span x-text="option.label" class="min-w-0 break-words"></span>
                            <x-lucide-check x-show="option.selected" class="size-4 shrink-0" aria-hidden="true" />
                        </li>
                    </template>
                </ul>
                <p x-show="!filtered.length" class="px-3 py-2 text-sm text-ink-muted">Sin resultados</p>
            </div>
        @elseif ($isTextarea)
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
                {{ $control->class($controlClasses) }} @if ($maxlength !== null) maxlength="{{ $maxlength }}" @endif
                @if ($hasCount) x-on:input="update" @endif
                @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif @required($required)
                @disabled($disabled) @readonly($readonly) @if ($required) aria-required="true" @endif
                @if ($error !== null) aria-invalid="true" @endif
                @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>{{ $current }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
                value="{{ $current }}" {{ $control->class($controlClasses) }}
                @if ($hasToggle) x-bind:type="type" @endif
                @if ($placeholder !== null) placeholder="{{ $placeholder }}" @endif @required($required)
                @disabled($disabled) @readonly($readonly)
                @if ($required) aria-required="true" @endif
                @if ($error !== null) aria-invalid="true" @endif
                @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
        @endif
        @if ($suffix !== null)
            <span class="shrink-0 text-md text-ink-muted">{{ $suffix }}</span>
        @endif
        @if ($hasToggle)
            {{-- A toggle button keeps its name and says its state with aria-pressed alone --}}
            <button type="button" x-on:click="toggle" aria-pressed="false"
                x-bind:aria-pressed="visible ? 'true' : 'false'" aria-label="Mostrar la contraseña"
                title="Mostrar la contraseña"
                class="-mr-1 inline-flex size-8 shrink-0 items-center justify-center rounded-full text-brand-ink transition hover:bg-brand-soft aria-pressed:bg-glass-2 aria-pressed:text-ink pointer-coarse:min-h-11 pointer-coarse:min-w-11">
                <x-lucide-eye aria-hidden="true" class="size-4.5" x-show="!visible" />
                <x-lucide-eye-off aria-hidden="true" class="size-4.5" x-show="visible" x-cloak />
            </button>
        @endif
        @isset($trailing)
            {{ $trailing }}
        @endisset
    </div>
    @if ($hint !== null)
        <p id="{{ $id }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    @if ($hasCount)
        <p class="text-right text-sm text-ink-muted"><span id="{{ $id }}-count"
                x-text="text">{{ mb_strlen($current) }} de {{ $maxlength }} caracteres</span></p>
    @endif
    @if ($error !== null)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">
            <x-lucide-circle-alert class="size-4" aria-hidden="true" />{{ $error }}
        </p>
    @endif
</div>
