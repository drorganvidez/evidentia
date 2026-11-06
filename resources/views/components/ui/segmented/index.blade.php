{{--
    Segmented control: native radios in a pill, for an exclusive choice (meeting type, modality, status, theme).
    <x-ui.segmented name="type" legend="Tipo de reunión" :options="[['value' => 1, 'label' => 'Ordinaria'], ['value' => 2, 'label' => 'Extraordinaria']]" :value="$meeting->type" />
    <x-ui.segmented name="theme" legend="Tema" icon-only>
        <x-ui.segmented.option name="theme" value="light" label="Claro" icon="sun" x-bind:checked="$store.theme.is('light')" ... />
    </x-ui.segmented>
    old(name) wins over value; the error links to the fieldset through aria-describedby.
--}}
@props([
    'name' => null,
    'legend' => null,
    'hideLegend' => true,
    'options' => [],
    'value' => null,
    'size' => 'md',
    'iconOnly' => false,
    'block' => false,
    'error' => null,
    'id' => null,
])
@php
    throw_if(blank($name), InvalidArgumentException::class, 'The segmented control needs a name.');
    throw_if(blank($legend), InvalidArgumentException::class, 'The segmented control needs a legend.');
    $id ??= ui_field_id($name);
    $current = old($name, $value);
@endphp
<fieldset
    {{ $attributes->merge(array_filter(['id' => $id, 'tabindex' => '-1', 'class' => Arr::toCssClasses(['grid gap-1.5', 'w-full' => $block, 'justify-items-start' => !$block]), 'aria-describedby' => $error !== null ? $id . '-error' : null])) }}>
    <legend @class([
        'sr-only' => $hideLegend,
        'mb-1.5 text-sm font-semibold text-ink' => !$hideLegend,
    ])>{{ $legend }}</legend>
    <div @class([
        'inline-flex max-w-full gap-0.5 overflow-x-auto rounded-full border border-glass-edge bg-field p-0.75 no-scrollbar',
        'flex w-full [&>label]:flex-1 [&>label>span]:w-full [&>label>span]:justify-center' => $block,
    ])>
        @if ($options !== [])
            @foreach ($options as $option)
                <x-ui.segmented.option :name="$name" :value="$option['value']" :label="$option['label']" :icon="$option['icon'] ?? null"
                    :checked="$current !== null && (string) $option['value'] === (string) $current" :icon-only="$iconOnly" :size="$size" />
            @endforeach
        @else
            {{ $slot }}
        @endif
    </div>
    @if ($error !== null)
        <p id="{{ $id }}-error" class="text-sm font-medium text-rejected-ink">{{ $error }}</p>
    @endif
</fieldset>
