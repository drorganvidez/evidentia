{{--
    Item of <x-ui.menu>: a link, a button, a form that posts (method) or a separator (divider).
    <x-ui.menu.item href="{{ route('lecture.import') }}" icon="import">Importación masiva</x-ui.menu.item>
    <x-ui.menu.item icon="log-out" method="post" :action="route('logout')">Salir</x-ui.menu.item>
    <x-ui.menu.item tone="danger" icon="trash-2" method="delete" action="...">Eliminar</x-ui.menu.item>
    <x-ui.menu.item divider />
--}}
@props([
    'href' => null,
    'icon' => null,
    'tone' => 'default',
    'method' => null,
    'action' => null,
    'divider' => false,
])
@php
    $classes = [
        'flex w-full items-center gap-3 rounded-sm px-3 py-2.5 text-left text-md font-medium no-underline transition pointer-coarse:min-h-11',
        match ($tone) {
            'danger' => 'text-rejected-ink hover:bg-rejected-soft focus:bg-rejected-soft',
            default => 'text-ink hover:bg-glass-2 focus:bg-glass-2',
        },
    ];
    $method = $method !== null ? strtolower((string) $method) : null;
@endphp
@if ($divider)
    <li role="separator" class="my-1 border-t border-rule"></li>
@elseif ($method !== null)
    <li role="none">
        <form method="post" action="{{ $action ?? $href }}">
            @csrf
            @if ($method !== 'post')
                @method(strtoupper($method))
            @endif
            <button type="submit" role="menuitem" tabindex="-1" {{ $attributes->class($classes) }}>
                @if ($icon !== null)
                    <x-ui.icon :name="$icon" class="size-4.5" />
                @endif
                {{ $slot }}
            </button>
        </form>
    </li>
@elseif ($href !== null)
    <li role="none">
        <a href="{{ $href }}" role="menuitem" tabindex="-1" {{ $attributes->class($classes) }}>
            @if ($icon !== null)
                <x-ui.icon :name="$icon" class="size-4.5" />
            @endif
            {{ $slot }}
        </a>
    </li>
@else
    <li role="none">
        <button type="button" role="menuitem" tabindex="-1" {{ $attributes->class($classes) }}>
            @if ($icon !== null)
                <x-ui.icon :name="$icon" class="size-4.5" />
            @endif
            {{ $slot }}
        </button>
    </li>
@endif
