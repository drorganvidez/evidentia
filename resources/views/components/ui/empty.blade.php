{{--
    Empty state: an icon bubble or a big code, a real heading, one sentence and optional actions.
    <x-ui.empty title="No hay evidencias" text="Cuando subas una aparecerá aquí."><x-slot:actions>...</x-slot:actions></x-ui.empty>
    <x-ui.empty code="404" title="No encontramos la página" :level="1" />
    The heading tag is printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@props([
    'icon' => 'inbox',
    'title' => null,
    'text' => null,
    'level' => 3,
    'size' => 'md',
    'code' => null,
])
@php
    throw_if(blank($title), InvalidArgumentException::class, 'The empty component needs a title.');
    $heading = 'h' . max(1, min(6, (int) $level));
@endphp
<div {{ $attributes->class(['grid justify-items-center gap-3 text-center', $size === 'sm' ? 'py-6' : 'py-12']) }}>
    @if ($code !== null)
        <span class="font-mono text-6xl font-bold tracking-tighter text-brand-ink">{{ $code }}</span>
    @else
        <span aria-hidden="true" class="grid size-12 place-items-center rounded-md bg-brand-soft text-brand-soft-ink">
            <x-ui.icon :name="$icon" class="size-6" />
        </span>
    @endif
    {!! '<' . $heading . ' class="text-base font-bold">' !!}{{ $title }}{!! '</' . $heading . '>' !!}
    @if ($text !== null)
        <p class="max-w-md text-md text-ink-muted">{{ $text }}</p>
    @endif
    @isset($actions)
        <div class="flex flex-wrap justify-center gap-3 pt-1">{{ $actions }}</div>
    @endisset
</div>
