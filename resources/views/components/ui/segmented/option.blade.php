{{--
    One radio of <x-ui.segmented>: the visible pill follows the input (peer) so checked and focus can style it.
    <x-ui.segmented.option name="type" value="1" label="Ordinaria" checked />
    <x-ui.segmented.option name="theme" value="dark" label="Oscuro" icon="moon" icon-only x-bind:checked="$store.theme.is('dark')" x-on:change="$store.theme.select('dark')" />
    name, icon-only and size are repeated from the parent (no @aware); the other attributes land on the input.
--}}
@props([
    'name' => null,
    'value' => null,
    'label' => null,
    'icon' => null,
    'checked' => false,
    'iconOnly' => false,
    'size' => 'md',
    'id' => null,
])
@php
    throw_if(blank($name), InvalidArgumentException::class, 'The segmented option needs a name.');
    throw_if($value === null || $value === '', InvalidArgumentException::class, 'The segmented option needs a value.');
    throw_if(blank($label), InvalidArgumentException::class, 'The segmented option needs a label.');
    $id ??= 'field-' . Str::slug($name) . '-' . Str::slug((string) $value);
    $box = [
        'inline-flex items-center gap-1.5 rounded-full font-semibold text-ink-muted transition peer-checked:bg-glass-2 peer-checked:text-ink peer-checked:shadow-raised peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-hi pointer-coarse:min-h-11',
        match ($size) {
            'sm' => $iconOnly ? 'size-6.5 justify-center pointer-coarse:min-w-11' : 'min-h-6.5 px-2 text-2xs',
            default => $iconOnly ? 'size-7.5 justify-center pointer-coarse:min-w-11' : 'min-h-7.5 px-2.75 text-xs',
        },
    ];
    $iconClass = $size === 'sm' ? 'size-3.5' : 'size-4';
@endphp
<label class="relative" @if ($iconOnly) title="{{ $label }}" @endif>
    <input type="radio" name="{{ $name }}" value="{{ $value }}" id="{{ $id }}"
        class="peer sr-only" @checked($checked) {{ $attributes }}>
    <span @class($box)>
        @if ($icon !== null)
            <x-ui.icon :name="$icon" :class="$iconClass" />
        @endif
        @if ($iconOnly)
            <span class="sr-only">{{ $label }}</span>
        @else
            <span>{{ $label }}</span>
        @endif
    </span>
</label>
