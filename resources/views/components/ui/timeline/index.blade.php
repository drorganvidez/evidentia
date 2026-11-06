{{--
    Steps of a process (variant steps) or a log of events (variant log), as an ordered list of x-ui.timeline.item.
    <x-ui.timeline variant="steps" label="Recorrido de la evidencia" :columns="4">
        <x-ui.timeline.item state="done" title="Borrador" :at="$evidence->created_at" />
        <x-ui.timeline.item state="current" title="Enviada" :at="$evidence->sent_at" />
    </x-ui.timeline>
    <x-ui.timeline label="Historial">
        <x-ui.timeline.item tone="rejected" title="Rechazada" :at="$at">por <a href="...">Carlos Navarro</a></x-ui.timeline.item>
    </x-ui.timeline>
    Without columns the steps share the row in equal columns from md.
--}}
@props(['variant' => 'log', 'label' => null, 'columns' => null])
@php
    throw_if(blank($label), InvalidArgumentException::class, 'The timeline component needs a label.');
    $classes =
        $variant === 'steps'
            ? [
                'glass m-0 grid list-none gap-0 rounded-xl px-5 py-4 md:gap-3 md:px-8 md:py-6',
                match ((int) $columns) {
                    2 => 'md:grid-cols-2',
                    3 => 'md:grid-cols-3',
                    4 => 'md:grid-cols-4',
                    5 => 'md:grid-cols-5',
                    6 => 'md:grid-cols-6',
                    default => 'md:grid-flow-col md:auto-cols-fr',
                },
            ]
            : ['m-0 grid list-none gap-2'];
@endphp
<ol aria-label="{{ $label }}" {{ $attributes->class($classes) }}>
    {{ $slot }}
</ol>
