{{--
    Checkbox, radio or switch inside its label: one big target, an optional hint and an error. CSS only (peer), no Alpine.
    <x-ui.choice name="remember" label="Recordarme" hint="Durante 30 días en este dispositivo" />
    <x-ui.choice type="radio" name="participation" value="online" label="En línea" />
    <x-ui.choice type="switch" name="allow_access" label="Permitir acceso a la aplicación" :checked="$user->allow_access" />
    checked rules the first load only. Once a failed validation brings the input back, old() decides and a missing key
    means unticked (a radio compares it with value, a checkbox named name[] accepts arrays); inside a GET x-ui.form old()
    is never read. The consumer class goes to the root; every other attribute (x-on:change, data-*) lands on the input,
    which covers the whole label.
--}}
@props([
    'type' => 'checkbox',
    'name' => '',
    'value' => '1',
    'label' => '',
    'checked' => null,
    'hint' => null,
    'error' => null,
    'disabled' => false,
    'id' => null,
])
@aware(['method' => null])
@php
    throw_if(blank($name), InvalidArgumentException::class, 'The choice component needs a name.');
    throw_if(blank($label), InvalidArgumentException::class, 'The choice component needs a label.');

    $type = in_array($type, ['checkbox', 'radio', 'switch'], true) ? $type : 'checkbox';
    $isRadio = $type === 'radio';
    $isSwitch = $type === 'switch';
    $isArray = str_ends_with($name, '[]');
    $value = (string) $value;
    $errorKey = ui_error_key($name);
    $id ??= ui_field_id($name) . ($isRadio || $isArray ? '-' . Str::slug($value) : '');

    // Never assume $errors: the components also render through Blade::render
    $bag = $errors ?? null;
    if ($error === null && $bag instanceof \Illuminate\Support\ViewErrorBag) {
        $error = $bag->first($errorKey) ?: ($bag->first($errorKey . '.*') ?: null);
    }

    // After a failed validation the input that came back decides, absent keys included: an unticked box is not sent
    $refilled =
        strtolower((string) $method) !== 'get' && request()->hasSession() && request()->session()->hasOldInput();
    if ($refilled) {
        $old = old($errorKey);
        $checked = is_array($old)
            ? in_array($value, array_map('strval', $old), true)
            : $old !== null && (string) $old === $value;
    }

    $describedBy = implode(
        ' ',
        array_filter([$error !== null ? $id . '-error' : null, $hint !== null ? $id . '-hint' : null]),
    );
    $boxClasses = match ($type) {
        'radio'
            => "relative mt-0.5 grid size-5 shrink-0 place-items-center rounded-full border border-field-edge bg-field transition peer-checked:border-brand peer-checked:bg-brand peer-focus-visible:outline-2 peer-focus-visible:outline-offset-3 peer-focus-visible:outline-brand-hi peer-disabled:opacity-50 after:size-2 after:rounded-full after:bg-on-brand after:opacity-0 after:transition after:content-[''] peer-checked:after:opacity-100",
        'switch'
            => "relative mt-0.5 h-5.5 w-9.5 shrink-0 rounded-full bg-slate-500 transition peer-checked:bg-aurora-500 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-3 peer-focus-visible:outline-brand-hi peer-disabled:opacity-50 after:absolute after:top-0.75 after:left-0.75 after:size-4 after:rounded-full after:bg-white after:shadow-[0_1px_3px_rgb(0_0_0/.25)] after:transition after:content-[''] peer-checked:after:translate-x-4",
        default
            => 'mt-0.5 grid size-5 shrink-0 place-items-center rounded-xs border border-field-edge bg-field transition peer-checked:border-brand peer-checked:bg-brand peer-focus-visible:outline-2 peer-focus-visible:outline-offset-3 peer-focus-visible:outline-brand-hi peer-disabled:opacity-50 [&>svg]:opacity-0 peer-checked:[&>svg]:opacity-100',
    };
    $input = $attributes->except('class');
@endphp
<div {{ $attributes->only('class')->class(['grid grid-cols-[minmax(0,1fr)] gap-1']) }}>
    <label for="{{ $id }}"
        class="relative inline-flex min-h-11 cursor-pointer items-start gap-3 py-1 has-[:disabled]:cursor-not-allowed">
        <input id="{{ $id }}" type="{{ $isRadio ? 'radio' : 'checkbox' }}" name="{{ $name }}"
            value="{{ $value }}"
            {{ $input->class(['peer absolute inset-0 size-full cursor-pointer appearance-none opacity-0 disabled:cursor-not-allowed']) }}
            @if ($isSwitch) role="switch" @endif @checked($checked)
            @disabled($disabled) @if ($error !== null) aria-invalid="true" @endif
            @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
        <span aria-hidden="true" class="{{ $boxClasses }}">
            @if (!$isRadio && !$isSwitch)
                <x-lucide-check class="size-3.5 text-on-brand" />
            @endif
        </span>
        <span class="grid peer-disabled:text-ink-muted">
            <span class="text-md font-medium">{{ $label }}</span>
            @if ($hint !== null)
                <span id="{{ $id }}-hint" class="text-sm text-ink-muted">{{ $hint }}</span>
            @endif
        </span>
    </label>
    @if ($error !== null)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">
            <x-lucide-circle-alert class="size-4" aria-hidden="true" />{{ $error }}
        </p>
    @endif
</div>
