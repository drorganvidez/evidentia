{{--
    Main navigation: a sidebar list that compacts to a rail between md and lg, the rail on its own, or the fixed
    bottom bar of the phone.
    <x-ui.nav :items="$nav['main']" />
    <x-ui.nav :items="$nav['management']" heading="Gestión" label="Gestión" />
    <x-ui.nav variant="bar" :items="$mobileNav" />
    An item is ['label' => 'Mis evidencias', 'href' => '/evidencias', 'icon' => 'file-text', 'current' => true,
    'count' => 6, 'countLabel' => 'pendientes']. In the rail a word longer than the link breaks (hyphenated where the
    browser can) instead of widening it.
--}}
@props([
    'items' => [],
    'variant' => 'sidebar',
    'label' => null,
    'heading' => null,
    'rail' => true,
])
@php
    $label ??= $variant === 'bar' ? 'Navegación principal (móvil)' : 'Navegación principal';
    // The rail is the md to lg state of the sidebar; variant="rail" applies it at every width.
    $compact = $variant === 'rail' || ($variant === 'sidebar' && $rail);
    $entry = match ($variant) {
        'rail'
            => 'group relative flex flex-col gap-1 rounded-sm px-1 py-2 text-center text-2xs leading-tight font-medium text-ink no-underline transition hover:bg-glass-2 current:bg-brand-soft current:text-brand-soft-ink current:inset-shadow-hi pointer-coarse:min-h-11',
        default => [
            'group relative flex items-center gap-3 rounded-sm px-3 py-2.5 text-md font-medium text-ink no-underline transition hover:bg-glass-2 current:bg-brand-soft current:text-brand-soft-ink current:inset-shadow-hi pointer-coarse:min-h-11',
            'md:max-lg:flex-col md:max-lg:gap-1 md:max-lg:px-1 md:max-lg:py-2 md:max-lg:text-center md:max-lg:text-2xs md:max-lg:leading-tight' => $rail,
        ],
    };
    $icon = match ($variant) {
        'rail' => 'size-5.5 group-current:text-brand-ink',
        default => ['size-4.5 group-current:text-brand-ink', 'md:max-lg:size-5.5' => $rail],
    };
    // In the rail the label reaches into the side padding of the link (62 px instead of 54) and only a longer word
    // breaks; anywhere also lowers its min-content width, so the word never pushes the rail open
    $text = match ($variant) {
        'rail' => 'min-w-0 -mx-1 wrap-anywhere hyphens-auto',
        default => ['min-w-0 wrap-anywhere hyphens-auto', 'md:max-lg:-mx-1' => $rail],
    };
    $count = match ($variant) {
        'rail'
            => 'absolute top-1 right-1 rounded-full bg-glass-2 px-1.5 py-0.5 text-2xs font-semibold text-ink-muted tabular-nums',
        default => [
            'ml-auto rounded-full bg-glass-2 px-2 py-0.5 text-xs font-semibold text-ink-muted tabular-nums',
            'md:max-lg:absolute md:max-lg:top-1 md:max-lg:right-1 md:max-lg:ml-0 md:max-lg:px-1.5 md:max-lg:text-2xs' => $rail,
        ],
    };
@endphp
@if ($variant === 'bar')
    <nav aria-label="{{ $label }}"
        {{ $attributes->class(['glass fixed inset-x-3 bottom-safe z-30 rounded-lg p-1.5 md:hidden print-hidden']) }}>
        <ul class="grid grid-cols-5 gap-0.5">
            @foreach ($items as $item)
                <li class="min-w-0">
                    <a href="{{ $item['href'] }}" @if ($item['current'] ?? false) aria-current="page" @endif
                        class="grid min-w-0 justify-items-center gap-0.75 rounded-sm px-0.5 py-1.75 text-2xs font-semibold text-ink-muted no-underline current:bg-brand-soft current:text-brand-ink pointer-coarse:min-h-11">
                        <x-ui.icon :name="$item['icon']" class="size-5.5" />
                        <span class="max-w-full truncate">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@else
    <nav aria-label="{{ $label }}"
        {{ $attributes->class(['mt-2 border-t border-rule pt-4' => $heading !== null])->filter(fn($value) => $value !== '') }}>
        @if ($heading !== null)
            <p @class([
                'mb-2 ml-3 text-2xs font-bold tracking-widest text-ink-muted uppercase',
                'sr-only' => $variant === 'rail',
                'md:max-lg:sr-only' => $compact && $variant !== 'rail',
            ])>{{ $heading }}</p>
        @endif
        <ul class="grid gap-0.75">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item['href'] }}" @if ($item['current'] ?? false) aria-current="page" @endif
                        @class($entry)>
                        <x-ui.icon :name="$item['icon']" @class($icon) />
                        <span @class($text)>{{ $item['label'] }}</span>
                        @if (($item['count'] ?? null) !== null)
                            @php
                                // Escaped here so the hidden label can sit flush against the figure
                                $hiddenLabel =
                                    ($item['countLabel'] ?? null) !== null
                                        ? '<span class="sr-only"> ' . e($item['countLabel']) . '</span>'
                                        : '';
                            @endphp
                            <span @class($count)>{{ $item['count'] }}{!! $hiddenLabel !!}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
