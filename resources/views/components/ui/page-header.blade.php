{{--
    Title row of a screen: the only h1 of the page, an optional lede, an eyebrow above, a meta line below and the actions.
    <x-ui.page-header title="Hola, Juan" lede="Tienes dos evidencias pendientes de revisión.">
        <x-slot:eyebrow><x-ui.badge tone="pending">Pendiente</x-ui.badge></x-slot:eyebrow>
        <x-slot:meta><x-ui.deadline variant="inline" ... /></x-slot:meta>
        <x-slot:actions><x-ui.button variant="primary" icon="plus">Nueva evidencia</x-ui.button></x-slot:actions>
    </x-ui.page-header>
    The layout points main at the id page-title; pass another id only when a page shows several headers.
    Its texts use ink, not ink-muted: the header sits on the bare canvas, where the aurora blobs drift under it.
    The heading tag is printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@props(['title' => null, 'level' => 1, 'lede' => null, 'id' => 'page-title'])
@php
    throw_if(blank($title), InvalidArgumentException::class, 'The page header needs a title.');
    $heading = 'h' . max(1, min(6, (int) $level));
@endphp
<header
    {{ $attributes->class(['flex flex-col items-stretch gap-4 px-2 md:flex-row md:items-end md:justify-between md:gap-8 md:px-3']) }}>
    <div class="min-w-0">
        @isset($eyebrow)
            <div {{ $eyebrow->attributes->class(['mb-2 flex flex-wrap items-center gap-2 text-sm font-medium text-ink']) }}>
                {{ $eyebrow }}</div>
        @endisset
        {!! '<' . $heading . ' id="' . e($id) . '" class="text-4xl font-bold tracking-tighter md:text-5xl">' !!}{{ $title }}{!! '</' . $heading . '>' !!}
        @if ($lede !== null)
            <p class="mt-2 max-w-[56ch] text-md text-ink md:text-base">{{ $lede }}</p>
        @endif
        @isset($meta)
            <div {{ $meta->attributes->class(['mt-3 lg:hidden']) }}>{{ $meta }}</div>
        @endisset
    </div>
    @isset($actions)
        <div
            {{ $actions->attributes->class(['flex flex-col gap-3 md:shrink-0 md:flex-row md:items-center md:justify-end']) }}>
            {{ $actions }}</div>
    @endisset
</header>
