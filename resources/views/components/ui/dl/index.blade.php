{{--
    Key-value pairs as a real definition list. The term width and the row padding reach the items as CSS variables.
    <x-ui.dl>
        <x-ui.dl.item term="Registro" mono>EV-0033</x-ui.dl.item>
        <x-ui.dl.item term="Horas"><x-ui.hours :value="1.75" /></x-ui.dl.item>
    </x-ui.dl>
    <x-ui.dl term-width="sm" dense>...</x-ui.dl>
--}}
@props(['termWidth' => 'md', 'dense' => false])
@php
    $classes = [
        'grid',
        match ($termWidth) {
            'sm' => '[--term-w:5rem]',
            default => '[--term-w:5.75rem] md:[--term-w:6.25rem]',
        },
        $dense ? '[--dl-py:0.375rem]' : '[--dl-py:0.5625rem]',
    ];
@endphp
<dl {{ $attributes->class($classes) }}>
    {{ $slot }}
</dl>
