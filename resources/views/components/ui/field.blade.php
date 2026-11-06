{{--
    Labelled form control: input, select or textarea with hint, error, icon, prefix or suffix and the server values.
    <x-ui.field name="title" label="Título" required hint="Mínimo 5 caracteres" />
    <x-ui.field name="hours" label="Horas" type="number" suffix="h" inputmode="decimal" min="0" step="0.25" />
    <x-ui.field name="committee" label="Comité" type="select" :options="$committees" placeholder="Elige un comité" />
    <x-ui.field name="description" label="Descripción" type="textarea" :rows="4" :maxlength="1000" />
    <x-ui.field name="password" label="Contraseña" type="password" size="lg" autocomplete="current-password" />
    The consumer class goes to the root (layout); every other attribute (min, max, step, autocomplete, inputmode,
    pattern, x-model) lands on the control. old() wins over value; the error comes from $errors or the error prop.
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
    $current = old($errorKey) ?? $value;
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
        'cursor-pointer appearance-none pr-8' => $isSelect,
        'resize-none field-sizing-content min-h-[calc(var(--rows)*1.5rem)] max-h-[50dvh]' => $isTextarea,
        $rowsClass => $isTextarea,
    ];
    $control = $attributes->except('class');
    $root = $attributes->only('class')->class(['grid grid-cols-[minmax(0,1fr)] gap-1.5']);
    if ($hasToggle) {
        $root = $root->merge(['x-data' => 'passwordField']);
    } elseif ($hasCount) {
        $root = $root->merge(['x-data' => 'charCount']);
    }
@endphp
<div {{ $root }}>
    <label for="{{ $id }}" class="{{ $hideLabel ? 'sr-only' : 'text-sm font-semibold' }}">{{ $label }}
        @if ($required)
            <span aria-hidden="true" class="text-rejected-ink after:content-['*']"></span>
        @endif
    </label>
    <div
        class="{{ Arr::toCssClasses([
            'relative flex gap-2 rounded-md border border-field-edge bg-field pl-4 pr-3 transition focus-within:border-aurora-500 focus-within:bg-glass-2 focus-within:ring-4 focus-within:ring-aurora-500/18 has-[[aria-invalid=true]]:border-rejected has-[[aria-invalid=true]]:focus-within:ring-rejected/18 has-[:disabled]:border-dashed has-[:disabled]:bg-rule/40',
            $heightClass,
        ]) }}">
        @if ($icon !== null)
            <x-ui.icon :name="$icon" class="size-4.5 text-ink-muted" />
        @endif
        @if ($prefix !== null)
            <span class="shrink-0 text-md text-ink-muted">{{ $prefix }}</span>
        @endif
        @if ($isSelect)
            <select id="{{ $id }}" name="{{ $name }}" {{ $control->class($controlClasses) }}
                @required($required) @disabled($disabled)
                @if ($required) aria-required="true" @endif
                @if ($error !== null) aria-invalid="true" @endif
                @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                @if ($placeholder !== null)
                    <option value="" disabled @selected($current === '')>{{ $placeholder }}</option>
                @endif
                @foreach ($items as $item)
                    <option value="{{ $item['value'] }}" @selected($current !== '' && $current === $item['value']) @disabled($item['disabled'])>
                        {{ $item['label'] }}</option>
                @endforeach
            </select>
            <x-lucide-chevron-down class="pointer-events-none absolute right-3 size-4 text-ink-muted"
                aria-hidden="true" />
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
            <button type="button" x-on:click="toggle" aria-pressed="false"
                x-bind:aria-pressed="visible ? 'true' : 'false'" aria-label="Mostrar la contraseña"
                x-bind:aria-label="label" title="Mostrar la contraseña" x-bind:title="label"
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
