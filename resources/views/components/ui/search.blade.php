{{--
    Search form of the topbar: a GET form with the "/" shortcut that focuses its field (shortcut.js).
    <x-ui.search :action="route('evidence.list')" />
    <x-ui.search :action="route('evidence.list')" :hide-on-phone="false" id="field-q-drawer" />   inside the drawer
    Two instances on a page need different ids; the value defaults to the current query string. Once the person
    turns the shortcuts off ($store.shortcuts) the key does nothing and the hint and aria-keyshortcuts go away.
    Between md and xl the topbar is tight, so the field narrows there and leaves the breadcrumbs room.
--}}
@props([
    'action' => null,
    'placeholder' => 'Buscar evidencias',
    'name' => 'q',
    'value' => null,
    'shortcut' => '/',
    'label' => 'Buscar',
    'id' => null,
    'hideOnPhone' => true,
])
@php
    throw_if(blank($action), InvalidArgumentException::class, 'The search needs an action.');
    $id ??= 'field-' . Str::slug($name);
    // ?q[]=x reaches the query string as an array: only a scalar fills the field (same rule as field)
    $query = request()->query($name);
    $value ??= is_scalar($query) ? (string) $query : '';
@endphp
<form role="search" method="get" action="{{ $action }}" x-data="shortcut" data-key="{{ $shortcut }}"
    x-on:keydown.window="onKey" {{ $attributes->class(['hidden md:block' => $hideOnPhone]) }}>
    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <div
        class="flex h-9 min-w-55 items-center gap-2 rounded-full border border-field-edge bg-field pr-2 pl-3 transition pointer-coarse:h-auto focus-within:border-aurora-500 focus-within:ring-4 focus-within:ring-aurora-500/18 md:max-xl:min-w-40">
        <x-lucide-search class="size-4.5 shrink-0 text-ink-muted" aria-hidden="true" />
        <input id="{{ $id }}" type="search" name="{{ $name }}" value="{{ $value }}"
            x-ref="input" aria-keyshortcuts="{{ $shortcut }}" x-bind:aria-keyshortcuts="keys"
            placeholder="{{ $placeholder }}" autocomplete="off"
            class="w-0 min-w-0 flex-1 self-stretch bg-transparent text-control text-ink outline-none placeholder:text-ink-muted pointer-coarse:min-h-11">
        <kbd aria-hidden="true" x-show="enabled"
            class="rounded-xs border border-rule px-1.5 py-px font-mono text-2xs font-semibold text-ink-muted">{{ $shortcut }}</kbd>
    </div>
</form>
