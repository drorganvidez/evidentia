{{--
    Menu button (APG pattern) for commands: a trigger and a popup of menu items with roving focus.
    <x-ui.menu label="Acciones" icon-only>
        <x-ui.menu.item href="{{ route('lecture.import') }}" icon="import">Importación masiva</x-ui.menu.item>
        <x-ui.menu.item divider />
        <x-ui.menu.item tone="danger" icon="trash-2" method="post" action="...">Borrar todos los usuarios</x-ui.menu.item>
    </x-ui.menu>
    A trigger slot replaces the button; it must carry x-ref="button" x-on:click="toggle" x-on:keydown.down.prevent="openAndFocusFirst"
    x-bind:aria-expanded="open ? 'true' : 'false'" aria-haspopup="menu" and aria-controls with the id. The focus leaving
    the root closes the menu, from the trigger as well as from the list.
    Never for navigation between pages (that is nav or tabs) nor inside containers with overflow. A glass card is a
    stacking context (backdrop blur): give it relative z-10 when its popup has to paint over the cards that follow.
--}}
@props([
    'label' => null,
    'icon' => null,
    'iconOnly' => false,
    'variant' => 'quiet',
    'size' => 'md',
    'align' => 'end',
    'id' => null,
])
@php
    throw_if(blank($label), InvalidArgumentException::class, 'The menu needs a label.');
    $id ??= 'menu-' . Str::random(6);
    $icon ??= $iconOnly ? 'ellipsis' : null;
@endphp
<div x-data="menu" x-on:keydown.escape.prevent="close(true)" x-on:focusout="onFocusOut"
    {{ $attributes->class(['relative inline-block']) }}>
    @isset($trigger)
        {{ $trigger }}
    @else
        <x-ui.button :variant="$variant" :size="$size" :icon="$icon" :icon-only="$iconOnly" :label="$label" x-ref="button"
            aria-haspopup="menu" aria-controls="{{ $id }}" aria-expanded="false"
            x-bind:aria-expanded="open ? 'true' : 'false'" x-on:click="toggle" x-on:keydown.down.prevent="openAndFocusFirst"
            x-on:keydown.up.prevent="openAndFocusLast">{{ $label }}</x-ui.button>
    @endisset
    {{-- hidden instead of x-show: x-bind applies at once, so the focus can move in right after opening.
         tabindex -1: a click on a part that is not an item (the header of the user menu) keeps the focus in the root --}}
    <ul id="{{ $id }}" role="menu" aria-label="{{ $label }}" tabindex="-1" x-ref="menu" hidden
        x-bind:hidden="!open" x-on:keydown="onKeydown" x-on:click.outside="onOutsideClick"
        x-on:click="onItemClick" @class([
            'glass absolute top-[calc(100%+0.5rem)] z-20 min-w-56 max-w-[calc(100vw-1.5rem)] rounded-lg p-1.5',
            $align === 'start' ? 'left-0' : 'right-0',
        ])>
        {{ $slot }}
    </ul>
</div>
