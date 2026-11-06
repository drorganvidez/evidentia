{{--
    Brand mark and name, linked to the home page. The mark is decorative (its letter is CSS content, so it never
    counts as visible text); the link is named after the visible text plus «inicio» (WCAG 2.5.3), or after label.
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
        'sm' => ['size-6.5 rounded-xs text-md', 'text-sm'],
        'lg' => ['size-12 rounded-md text-3xl', 'text-2xl'],
        default => ['size-10.5 rounded-[0.8125rem] text-2xl', 'text-xl'],
    };
    $label ??= 'Evidentia' . ($subtitle !== null && !$markOnly ? ', ' . $subtitle : '') . ', inicio';
@endphp
<a href="{{ $href }}" aria-label="{{ $label }}"
    {{ $attributes->class(['flex items-center gap-3 p-1 text-ink no-underline']) }}>
    <span aria-hidden="true"
        class="{{ $mark }} grid place-items-center bg-linear-135 from-aurora-500 to-cyan-400 to-[120%] font-extrabold tracking-tightest text-white shadow-glow inset-shadow-hi before:content-['e']"></span>
    @unless ($markOnly)
        <span>
            <span class="{{ $name }} block leading-tight font-bold tracking-tighter">Evidentia</span>
            @if ($subtitle !== null)
                <span class="block text-xs font-medium text-ink">{{ $subtitle }}</span>
            @endif
        </span>
    @endunless
</a>
