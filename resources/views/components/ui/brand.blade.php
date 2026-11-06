{{--
    Brand mark and name, linked to the home page. The mark is the drawing of public/favicon.svg, inline and decorative
    (aria-hidden); the link is named after the visible text plus «inicio» (WCAG 2.5.3), or after label.
    <x-ui.brand subtitle="Jornadas InnoSoft 2026" />
    <x-ui.brand size="sm" mark-only class="hidden md:max-lg:grid" />
    The subtitle uses ink, not ink-muted: the public header sits on the bare canvas, where the aurora drifts under it.
--}}
@props([
    'href' => url('/'),
    'size' => 'md',
    'subtitle' => null,
    'label' => null,
    'markOnly' => false,
])
@php
    [$mark, $name] = match ($size) {
        'sm' => ['size-6.5', 'text-sm'],
        'lg' => ['size-12', 'text-2xl'],
        default => ['size-10.5', 'text-xl'],
    };
    // Every mark of the page draws its own gradient: ids never repeat
    $gradient = 'brand-mark-' . Str::random(6);
    $label ??= 'Evidentia' . ($subtitle !== null && !$markOnly ? ', ' . $subtitle : '') . ', inicio';
@endphp
<a href="{{ $href }}" aria-label="{{ $label }}"
    {{ $attributes->class(['flex items-center gap-3 p-1 text-ink no-underline pointer-coarse:min-h-11 pointer-coarse:min-w-11']) }}>
    <svg aria-hidden="true" focusable="false" viewBox="0 0 64 64"
        class="{{ $mark }} shrink-0 rounded-[28%] shadow-glow">
        <defs>
            <linearGradient id="{{ $gradient }}" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#7d6af5" />
                <stop offset="1" stop-color="#4fc3e8" />
            </linearGradient>
        </defs>
        <rect width="64" height="64" rx="18" fill="url(#{{ $gradient }})" />
        <path fill="#fff"
            d="M32.83 51Q27.03 51 22.77 48.43Q18.5 45.87 16.17 41.53Q13.83 37.2 13.83 31.93Q13.83 26.47 16.27 22.2Q18.7 17.93 22.83 15.47Q26.97 13 32.17 13Q36.5 13 39.83 14.37Q43.17 15.73 45.47 18.2Q47.77 20.67 48.97 23.9Q50.17 27.13 50.17 30.93Q50.17 32 50.07 33.03Q49.97 34.07 49.7 34.8H22.43V27.47H44.03L39.3 30.93Q39.97 28.07 39.23 25.83Q38.5 23.6 36.67 22.3Q34.83 21 32.17 21Q29.57 21 27.7 22.27Q25.83 23.53 24.9 26Q23.97 28.47 24.17 32Q23.9 35.07 24.9 37.4Q25.9 39.73 27.97 41.03Q30.03 42.33 32.97 42.33Q35.63 42.33 37.53 41.27Q39.43 40.2 40.5 38.33L48.5 42.13Q47.43 44.8 45.13 46.8Q42.83 48.8 39.7 49.9Q36.57 51 32.83 51Z" />
    </svg>
    @unless ($markOnly)
        <span>
            <span class="{{ $name }} block leading-tight font-bold tracking-tighter">Evidentia</span>
            @if ($subtitle !== null)
                <span class="block text-xs font-medium text-ink">{{ $subtitle }}</span>
            @endif
        </span>
    @endunless
</a>
