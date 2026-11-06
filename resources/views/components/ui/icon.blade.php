{{--
    Icon with a dynamic name (lists, tables, statuses). For a fixed name write <x-lucide-check /> directly.
    <x-ui.icon name="check" class="size-4" />                 decorative, hidden from assistive technology
    <x-ui.icon name="triangle-alert" label="Aviso" class="size-5" />   announced with its label
--}}
@props(['name', 'label' => null, 'set' => 'lucide'])
@php
    $attrs = $attributes->merge(['class' => 'shrink-0'])->getAttributes();
    $attrs += $label !== null ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true'];
@endphp
{{ svg($set . '-' . $name, $attrs['class'], \Illuminate\Support\Arr::except($attrs, 'class')) }}
