{{--
    Button, or link that looks like one. Variants primary, quiet (default), danger and ghost; sizes sm, md and lg.
    <x-ui.button variant="primary" icon="plus" href="{{ route('evidence.create') }}">Nueva evidencia</x-ui.button>
    <x-ui.button icon-only icon="eye" label="Abrir Montaje del photocall" href="..." dim />
    <x-ui.button type="submit" :busy="$saving">Guardar</x-ui.button>
    <x-ui.button icon="copy" copy="https://evidentia.us.es/sign/4821">Copiar enlace</x-ui.button>
--}}
@props([
    'variant' => 'quiet',
    'size' => 'md',
    'icon' => null,
    'iconEnd' => false,
    'iconOnly' => false,
    'label' => null,
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'busy' => false,
    'block' => false,
    'copy' => null,
    'dim' => false,
    'pressed' => null,
])
@php
    if ($iconOnly && ($label === null || $label === '')) {
        throw new InvalidArgumentException('An icon-only button needs a label.');
    }

    $variantClasses = match ($variant) {
        'primary'
            => "relative overflow-hidden border-transparent bg-brand text-on-brand shadow-glow inset-shadow-hi hover:-translate-y-px hover:bg-brand-lo hover:shadow-glow-lg after:pointer-events-none after:absolute after:inset-x-0 after:top-0 after:bottom-1/2 after:bg-linear-to-b after:from-white/22 after:to-white/0 after:content-['']",
        'danger' => 'border-rule bg-transparent text-rejected-ink hover:bg-rejected-soft',
        'ghost' => 'border-transparent text-brand-ink hover:bg-brand-soft',
        default => 'glass-2 text-ink hover:bg-glass-hi',
    };
    $sizeClasses = match ($size) {
        'sm' => $iconOnly ? 'size-8 justify-center pointer-coarse:min-w-11' : 'h-9 px-4 text-sm',
        'lg' => $iconOnly ? 'size-11 justify-center' : 'h-12 px-6 text-base',
        default => $iconOnly ? 'size-9 justify-center pointer-coarse:min-w-11' : 'h-10.5 px-5 text-md',
    };
    $iconClasses = $size === 'sm' ? 'size-4 shrink-0' : 'size-4.5 shrink-0';
    $isLink = $href !== null;
    $tag = $isLink ? 'a' : 'button';
    $attrs = $attributes->class([
        'inline-flex items-center gap-2 whitespace-nowrap rounded-full border font-semibold no-underline transition active:translate-y-px active:scale-[.99] disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-pressed:bg-glass-2 aria-pressed:text-ink pointer-coarse:min-h-11',
        $variantClasses,
        $sizeClasses,
        'w-full justify-center' => $block,
        'opacity-55 group-hover:opacity-100 focus-visible:opacity-100' => $dim,
    ]);
@endphp
<{{ $tag }} {{ $attrs }}
    @if ($isLink) href="{{ $href }}" @else type="{{ $type }}" @endif
    @if ($disabled && $isLink) aria-disabled="true" tabindex="-1" @elseif ($disabled && !$busy) disabled @endif
    @if ($busy) aria-busy="true" aria-disabled="true" @endif
    @if ($iconOnly) aria-label="{{ $label }}" title="{{ $label }}" @endif
    @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
    @if ($copy !== null) x-data="clipboard" data-copy="{{ $copy }}" x-on:click="copy" @endif>
    @if ($busy && (!$iconEnd || $icon === null))
        <x-lucide-loader-circle aria-hidden="true" class="{{ $iconClasses }} animate-spin" />
    @elseif ($icon !== null && !$iconEnd)
        @if ($copy !== null)
            <x-ui.icon :name="$icon" class="{{ $iconClasses }}" x-show="!copied" />
            <x-lucide-check aria-hidden="true" class="{{ $iconClasses }}" x-show="copied" x-cloak />
        @else
            <x-ui.icon :name="$icon" class="{{ $iconClasses }}" />
        @endif
    @endif
    @unless ($iconOnly)
        {{ $slot }}
    @endunless
    @if ($icon !== null && $iconEnd)
        @if ($busy)
            <x-lucide-loader-circle aria-hidden="true" class="{{ $iconClasses }} animate-spin" />
        @elseif ($copy !== null)
            <x-ui.icon :name="$icon" class="{{ $iconClasses }}" x-show="!copied" />
            <x-lucide-check aria-hidden="true" class="{{ $iconClasses }}" x-show="copied" x-cloak />
        @else
            <x-ui.icon :name="$icon" class="{{ $iconClasses }}" />
        @endif
    @endif
    </{{ $tag }}>
