{{--
    Status, chip or count label. The text names the state; the dot only repeats it in colour.
    <x-ui.badge tone="pending" pulse>Pendiente de revisión</x-ui.badge>
    <x-ui.badge variant="chip" mono>EV-0033</x-ui.badge>
    <x-ui.badge variant="chip" :hue="150">Logística</x-ui.badge>
    <x-ui.badge variant="count">12</x-ui.badge>
--}}
@props([
    'variant' => 'status',
    'tone' => 'draft',
    'dot' => null,
    'pulse' => false,
    'size' => 'md',
    'mono' => false,
    'hue' => null,
    'icon' => null,
    'href' => null,
])
@php
    $dot ??= $variant === 'status';
    $showDot = $variant !== 'count' && ($hue !== null || $dot);
    $leading = $showDot || $icon !== null;
    $dotTone = $tone === 'neutral' ? 'draft' : $tone;
    $dotSize = $variant === 'status' ? $size : 'md';

    $classes = match ($variant) {
        'chip' => [
            'inline-flex items-center gap-1.75 rounded-full glass-2 text-sm font-medium text-ink',
            $leading ? 'py-1 pr-2.5 pl-2' : 'px-2.5 py-1',
        ],
        'count' => [
            'inline-flex rounded-full bg-rule px-1.75 py-px font-mono text-2xs font-semibold tabular-nums text-ink',
        ],
        default => [
            'inline-flex items-center gap-2 whitespace-nowrap rounded-full font-semibold',
            match ($tone) {
                'accepted' => 'bg-accepted-soft text-accepted-ink',
                'pending' => 'bg-pending-soft text-pending-ink',
                'rejected' => 'bg-rejected-soft text-rejected-ink',
                'notice' => 'bg-notice-soft text-notice-ink',
                'brand' => 'bg-brand-soft text-brand-soft-ink',
                'neutral' => 'bg-rule text-ink',
                default => 'bg-draft-soft text-draft-ink',
            },
            match ($size) {
                'sm' => 'px-2 py-0.5 text-2xs',
                'lg' => $leading ? 'py-2.5 pr-4.5 pl-3.5 text-base' : 'px-4.5 py-2.5 text-base',
                default => $leading ? 'py-1.25 pr-3 pl-2 text-sm' : 'px-3 py-1.25 text-sm',
            },
        ],
    };
    $classes['font-mono tabular-nums'] = $mono;
@endphp
@if ($href !== null)
    <a href="{{ $href }}" {{ $attributes->class([...$classes, 'transition hover:text-ink']) }}>
        @if ($showDot)
            <x-ui.dot :tone="$dotTone" :hue="$hue" :pulse="$pulse" :size="$dotSize" />
        @endif
        @if ($icon !== null)
            <x-ui.icon :name="$icon" class="size-3.5" />
        @endif
        {{ $slot }}
    </a>
@else
    <span {{ $attributes->class($classes) }}>
        @if ($showDot)
            <x-ui.dot :tone="$dotTone" :hue="$hue" :pulse="$pulse" :size="$dotSize" />
        @endif
        @if ($icon !== null)
            <x-ui.icon :name="$icon" class="size-3.5" />
        @endif
        {{ $slot }}
    </span>
@endif
