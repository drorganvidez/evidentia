{{--
    Page footer: brand and version, the help links in a fixed order and the institution; compact for the public pages.
    <x-ui.footer :version-href="route('updates.list')" />
    <x-ui.footer compact />
    Without a version evidentia_version() reads the .version file the Dockerfile writes; :version="false" hides the pill and
    :meta="false" the institution (Blade gives a prop its default when it receives null).
    A link is ['label' => 'Repositorio', 'href' => 'https://...', 'icon' => 'github', 'external' => true].
--}}
@props([
    'version' => null,
    'versionHref' => null,
    'links' => null,
    'meta' => 'Universidad de Sevilla · ETSII',
    'compact' => false,
])
@php
    $links ??= [
        [
            'label' => 'Repositorio',
            'href' => 'https://github.com/drorganvidez/evidentia',
            'icon' => 'github',
            'external' => true,
        ],
        [
            'label' => 'GPL-3.0',
            'href' => 'https://github.com/drorganvidez/evidentia/blob/main/LICENSE',
            'icon' => 'file-text',
            'external' => true,
        ],
        ['label' => 'Ayuda', 'href' => 'https://github.com/drorganvidez/evidentia#readme', 'icon' => 'circle-help'],
        ['label' => 'Sugerencias', 'href' => route('suggestionsmailbox'), 'icon' => 'message-square'],
    ];
    $version ??= evidentia_version();
    $version = $version === false || $version === null || $version === '' ? null : ltrim((string) $version, 'vV');
    $versionTitle = $version !== null ? 'Ver las novedades de la versión ' . $version : null;
    $pill =
        'inline-flex min-h-6 items-center rounded-full bg-brand-soft px-2 font-mono text-2xs font-semibold text-brand-soft-ink no-underline';
    $link = $compact
        ? 'inline-flex min-h-6 items-center gap-1.5 rounded-full px-2 text-ink-muted no-underline transition hover:text-ink pointer-coarse:min-h-11'
        : 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 font-medium text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink pointer-coarse:min-h-11';
@endphp
<footer
    {{ $attributes->class([
        'print-hidden',
        'glass mt-2 flex min-h-15 flex-wrap items-center gap-3 rounded-lg p-4 text-sm md:gap-6 md:px-5 md:py-3' => !$compact,
        'flex flex-wrap items-center justify-center gap-x-4 gap-y-1 px-4 py-4 text-xs text-ink-muted' => $compact,
    ]) }}>
    <span class="inline-flex items-center gap-2 font-semibold text-ink">
        @unless ($compact)
            <x-ui.brand size="sm" mark-only />
        @endunless
        <span>Evidentia</span>
        @if ($version !== null)
            @if ($versionHref !== null)
                <a href="{{ $versionHref }}" title="{{ $versionTitle }}"
                    class="{{ $pill }}">v{{ $version }}</a>
            @else
                <span class="{{ $pill }}">v{{ $version }}</span>
            @endif
        @endif
    </span>
    @if ($links !== [])
        <nav aria-label="Enlaces del pie">
            <ul class="flex flex-wrap items-center gap-1">
                @foreach ($links as $item)
                    <li>
                        <a href="{{ $item['href'] }}" class="{{ $link }}"
                            @if ($item['external'] ?? false) target="_blank" rel="noopener noreferrer" @endif>
                            @if (($item['icon'] ?? null) !== null && !$compact)
                                <x-ui.icon :name="$item['icon']" class="size-4" />
                            @endif
                            {{ $item['label'] }}
                            @if ($item['external'] ?? false)
                                <span class="sr-only">(se abre en una pestaña nueva)</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif
    @if ($meta !== false && $meta !== null)
        <span @class([
            'ml-0 w-full whitespace-nowrap text-ink-muted md:ml-auto md:w-auto' => !$compact,
            'whitespace-nowrap' => $compact,
        ])>{{ $meta }}</span>
    @endif
</footer>
