{{--
    Empty state: an icon bubble or a big code, a real heading, one sentence and optional actions.
    <x-ui.empty title="No hay evidencias" text="Cuando subas una aparecerá aquí."><x-slot:actions>...</x-slot:actions></x-ui.empty>
    <x-ui.empty code="404" title="No encontramos la página" :level="1" />
    size sm tightens it inside cards and lists; size lg makes it the content of a whole page (errors, maintenance).
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
    // lg is for a whole page (errors, maintenance): a bigger bubble, title and sentence; md keeps its markup
    $large = $size === 'lg';
    $padding = match ($size) {
        'sm' => 'py-6',
        'lg' => 'py-4',
        default => 'py-12',
    };
    $bubble = $large
        ? 'grid size-16 place-items-center rounded-lg bg-brand-soft text-brand-soft-ink'
        : 'grid size-12 place-items-center rounded-md bg-brand-soft text-brand-soft-ink';
    $glyph = $large ? 'size-8' : 'size-6';
    $headingClass = $large ? 'text-3xl font-bold tracking-tight' : 'text-base font-bold';
    $textClass = $large ? 'max-w-md text-lg text-ink-muted' : 'max-w-md text-md text-ink-muted';
@endphp
<div {{ $attributes->class(['grid justify-items-center gap-3 text-center', $padding]) }}>
    @if ($code !== null)
        <span class="font-mono text-6xl font-bold tracking-tighter text-brand-ink">{{ $code }}</span>
    @else
        <span aria-hidden="true" class="{{ $bubble }}">
            <x-ui.icon :name="$icon" class="{{ $glyph }}" />
        </span>
    @endif
    {!! '<' . $heading . ' class="' . $headingClass . '">' !!}{{ $title }}{!! '</' . $heading . '>' !!}
    @if ($text !== null)
        <p class="{{ $textClass }}">{{ $text }}</p>
    @endif
    @isset($actions)
        <div class="flex flex-wrap justify-center gap-3 pt-1">{{ $actions }}</div>
    @endisset
</div>
