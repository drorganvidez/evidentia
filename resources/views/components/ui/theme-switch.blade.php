{{--
    Light, dark or system theme as three radios bound to the theme store (the store applies data-theme and persists it).
    <x-ui.theme-switch class="hidden md:block" />                          icons only, in the topbar
    <x-ui.theme-switch name="theme-drawer" labels block />                 with the texts, in the drawer
    Every instance needs its own name: the ids of the radios derive from it.
--}}
@props(['name' => 'theme', 'labels' => false, 'size' => 'md', 'block' => false])
@php
    $modes = [['light', 'Claro', 'sun'], ['dark', 'Oscuro', 'moon'], ['system', 'Sistema', 'monitor']];
@endphp
<div x-data {{ $attributes }}>
    <x-ui.segmented :name="$name" legend="Tema" :icon-only="!$labels" :size="$size" :block="$block">
        @foreach ($modes as [$mode, $label, $icon])
            <x-ui.segmented.option :name="$name" :value="$mode" :label="$label" :icon="$icon"
                :icon-only="!$labels" :size="$size" x-bind:checked="$store.theme.is('{{ $mode }}')"
                x-on:change="$store.theme.select('{{ $mode }}')" />
        @endforeach
    </x-ui.segmented>
</div>
