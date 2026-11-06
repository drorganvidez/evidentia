{{--
    Key-value pairs as a real definition list. The term width and the row padding reach the items as CSS variables.
    <x-ui.dl>
        <x-ui.dl.item term="Registro" mono>EV-0033</x-ui.dl.item>
        <x-ui.dl.item term="Horas"><x-ui.hours :value="1.75" /></x-ui.dl.item>
    </x-ui.dl>
    <x-ui.dl term-width="sm" dense>...</x-ui.dl>
    The list is a size container: its pairs stack the value under the term while it is narrower than 13.5rem (a
    narrow aside). Its width comes from its parent, so in a flex row give it flex-1 or w-full.
--}}
@props(['termWidth' => 'md', 'dense' => false])
@php
    $classes = [
        '@container grid',
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
