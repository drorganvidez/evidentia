{{--
    Glass card with an optional title row, something to its right, a body and a footer.
    <x-ui.card title="Pruebas" :level="3"><x-slot:extra>3 archivos</x-slot:extra>...</x-ui.card>
    <x-ui.card title="Mis horas" href="{{ route('hours.mine') }}" arrow>...</x-ui.card>
    With href the whole card is one link named by its content, so nothing inside it may be a link or a button;
    an aria-label is only right when it repeats the visible text (WCAG 2.5.3).
    The root and heading tags are printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@props([
    'title' => null,
    'level' => 2,
    'as' => 'section',
    'href' => null,
    'ariaLabel' => null,
    'id' => null,
    'halo' => false,
    'padding' => 'md',
    'arrow' => false,
])
@php
    $tag = $href !== null ? 'a' : (in_array($as, ['article', 'section', 'div'], true) ? $as : 'section');
    $heading = 'h' . max(1, min(6, (int) $level));
    $titleId = $title !== null ? $id ?? 'card-' . Str::slug($title) : null;
    // ARIA forbids a name on a generic div; a section or article takes aria-label or its title as its name
    // and a link is named by its content unless an aria-label is given
    $named = $tag !== 'div';
    $root = $attributes
        ->class([
            'glass rounded-xl',
            'relative' => $arrow || $halo,
            'halo-aurora overflow-hidden' => $halo,
            'group block text-inherit no-underline transition hover:-translate-y-0.5' => $href !== null,
        ])
        ->merge(
            array_filter([
                'href' => $href,
                'aria-label' => $named ? $ariaLabel : null,
                'aria-labelledby' => $named && $tag !== 'a' && $ariaLabel === null ? $titleId : null,
            ]),
        );
    $paddingClass = match ($padding) {
        'sm' => 'px-5 py-4',
        'none' => 'p-0',
        default => 'px-5 py-4 md:px-8 md:py-6',
    };
    $titleRow = Arr::toCssClasses([
        'flex flex-wrap items-start justify-between gap-x-4 gap-y-1',
        'mb-4' => !$slot->isEmpty(),
    ]);
@endphp
{!! '<' . $tag . ' ' . $root . '>' !!}
@if ($arrow)
    <x-lucide-chevron-right aria-hidden="true"
        class="absolute top-3.5 right-3.5 size-4.5 text-ink-muted opacity-55 transition group-hover:translate-x-0.5 group-hover:opacity-100" />
@endif
<div class="{{ Arr::toCssClasses([$paddingClass, 'pr-10' => $arrow]) }}">
    @if (isset($header))
        <div {{ $header->attributes->class([$titleRow]) }}>{{ $header }}</div>
    @elseif ($title !== null || isset($extra))
        <div class="{{ $titleRow }}">
            @if ($title !== null)
                {!! '<' . $heading . ' id="' . e($titleId) . '" class="min-w-0 text-lg font-bold tracking-tight">' !!}{{ $title }}{!! '</' . $heading . '>' !!}
            @endif
            @isset($extra)
                <div class="ml-auto flex items-center gap-2">{{ $extra }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</div>
@isset($footer)
    <div {{ $footer->attributes->class(['border-t border-rule px-5 py-4 md:px-8']) }}>{{ $footer }}</div>
@endisset
{!! '</' . $tag . '>' !!}
