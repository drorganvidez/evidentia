{{--
    Bell of the topbar: a real link to the inbox with the unread count in its accessible name.
    <x-ui.bell :count="$unread" :href="route('notifications.index')" />
    The visible counter is decorative (99+ past ninety-nine); the hidden text names the link with the exact figure.
--}}
@props(['count' => 0, 'href' => null, 'label' => 'Avisos'])
@php
    throw_if(blank($href), InvalidArgumentException::class, 'The bell needs a href.');
    $count = max(0, (int) $count);
    $name = $count > 0 ? $label . ', ' . $count . ' sin leer' : $label;
    $shown = $count > 99 ? '99+' : (string) $count;
@endphp
<a href="{{ $href }}" title="{{ $name }}"
    {{ $attributes->class(['relative grid size-9 place-items-center rounded-full text-ink-muted transition hover:bg-glass-2 hover:text-ink pointer-coarse:size-11']) }}>
    <x-lucide-bell class="size-4.5" aria-hidden="true" />
    <span class="sr-only">{{ $name }}</span>
    @if ($count > 0)
        <span aria-hidden="true"
            class="absolute -top-0.5 -right-0.5 grid min-w-4.5 place-items-center rounded-full bg-brand px-1 font-mono text-2xs font-bold text-on-brand tabular-nums">{{ $shown }}</span>
    @endif
</a>
