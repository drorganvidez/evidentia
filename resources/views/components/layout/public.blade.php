{{--
    Layout of the public pages (access, password reset, public signature, block, errors): the intense aurora, a floating
    header with the brand and the theme, the notices under it, one centred column and the compact footer. A one-column
    page shows the pieces of the platform in its margins from lg (x-ui.scene variant="sides"); scene=false removes them.
    <x-layout.public title="Acceso" wide subtitle="Jornadas InnoSoft 2026">
        <x-slot:aside>...claim and count chips...</x-slot:aside>
        <x-ui.card>...form...</x-ui.card>
    </x-layout.public>
    <x-layout.public title="Página no encontrada"><x-ui.card>...</x-ui.card></x-layout.public>
    wide puts the aside to the left of the content on large screens; the page provides the h1 (the claim or the card title).
    centered puts a lone card in the middle of the screen (errors, maintenance) instead of under the header.
    It loads resources/js/public.js, the light entry; the notices component needs that entry to register it.
--}}
@props([
    'title' => null,
    'wide' => false,
    'intense' => true,
    'entry' => 'resources/js/public.js',
    'subtitle' => null,
    'version' => null,
    'showTheme' => true,
    'contentId' => 'main',
    'skipLabel' => 'Saltar al formulario',
    'centered' => false,
    'scene' => true,
])
<x-layout.base :title="$title" :entry="$entry">
    <x-slot:head>{{ $head ?? '' }}</x-slot:head>
    <x-ui.skip-link :target="'#' . $contentId">{{ $skipLabel }}</x-ui.skip-link>
    <x-ui.aurora :intense="$intense" />
    @if ($scene && !$wide)
        <x-ui.scene variant="sides" />
    @endif

    <header class="absolute inset-x-0 top-0 flex items-center justify-between gap-4 p-4 md:px-6 md:py-5">
        <x-ui.brand size="lg" :subtitle="$subtitle" />
        @if ($showTheme)
            <x-ui.theme-switch name="theme-public" />
        @endif
    </header>

    <x-ui.notices position="top" />

    <main id="{{ $contentId }}" tabindex="-1" @class([
        'mx-auto grid min-h-dvh w-[min(35rem,100%-2rem)] content-start items-center gap-8 pt-22 pb-18 outline-none' => !$centered,
        'mx-auto grid min-h-dvh w-[min(35rem,100%-2rem)] content-center items-center gap-8 pt-22 pb-18 outline-none' => $centered,
        'lg:w-[min(73.75rem,100%-3rem)] lg:grid-cols-[minmax(0,1.15fr)_minmax(22.5rem,28.75rem)] lg:content-center lg:gap-12 lg:py-24' => $wide,
    ])>
        @isset($aside)
            <div {{ $aside->attributes->class(['grid gap-6']) }}>{{ $aside }}</div>
        @endisset
        {{ $slot }}
    </main>

    @isset($footer)
        {{ $footer }}
    @else
        <x-ui.footer compact :version="$version" />
    @endisset
</x-layout.base>
