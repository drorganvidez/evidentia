{{--
    Initials of a person over the aurora gradient, or their picture. Decorative by default because the name
    is written beside it; pass :decorative="false" when the avatar stands alone.
    <x-ui.avatar name="Juan" surname="Pérez" />
    <x-ui.avatar name="Juan Pérez" size="lg" :decorative="false" />
    <x-ui.avatar name="Juan" surname="Pérez" src="/storage/avatars/juan.jpg" size="sm" />
--}}
@props(['name' => '', 'surname' => null, 'src' => null, 'size' => 'md', 'decorative' => true])
@php
    $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $surname = trim((string) $surname);
    $last = $surname !== '' ? $surname : $words[1] ?? '';
    $initials = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1) . mb_substr($last, 0, 1));
    $fullName = trim(trim($name) . ' ' . $surname);
    $aria = $decorative ? ['aria-hidden' => 'true'] : ['role' => 'img', 'aria-label' => $fullName];
    $classes = [
        'shrink-0 rounded-full',
        match ($size) {
            'sm' => 'size-6 text-[0.625rem] ring-0',
            'lg' => 'size-12 text-sm ring-1 ring-aurora-400/50 ring-offset-2 ring-offset-glass-2',
            default => 'size-9 text-xs ring-1 ring-aurora-400/50 ring-offset-2 ring-offset-glass-2',
        },
    ];
@endphp
@if ($src !== null)
    <img src="{{ $src }}" alt="{{ $decorative ? '' : $fullName }}"
        {{ $attributes->class([...$classes, 'object-cover']) }}>
@else
    <span
        {{ $attributes->class([...$classes, 'grid place-items-center bg-linear-135 from-aurora-400 to-cyan-400 font-bold text-white'])->merge($aria) }}>{{ $initials }}</span>
@endif
