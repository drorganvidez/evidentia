{{--
    Avatar, name and an optional role line; the name becomes a link with href, 44 px tall under a coarse pointer.
    <x-ui.person name="Juan" surname="Pérez" role="como coordinador de Programa" href="/usuarios/4" />
    <x-ui.person name="Lucía" surname="Pérez" size="sm" reverse />
--}}
@props([
    'name' => '',
    'surname' => null,
    'role' => null,
    'href' => null,
    'src' => null,
    'size' => 'md',
    'reverse' => false,
])
@php
    $surname = trim((string) $surname);
    $fullName = $reverse && $surname !== '' ? $surname . ', ' . trim($name) : trim(trim($name) . ' ' . $surname);
    $nameClasses = match ($size) {
        'sm' => 'text-sm font-semibold text-ink',
        default => 'text-md font-semibold text-ink',
    };
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-2']) }}>
    <x-ui.avatar :name="$name" :surname="$surname" :src="$src" :size="$size === 'sm' ? 'sm' : 'md'" />
    <span class="grid leading-tight">
        @if ($href !== null)
            <a href="{{ $href }}"
                class="{{ $nameClasses }} transition hover:text-brand-ink pointer-coarse:flex pointer-coarse:min-h-11 pointer-coarse:min-w-11 pointer-coarse:items-center">{{ $fullName }}</a>
        @else
            <span class="{{ $nameClasses }}">{{ $fullName }}</span>
        @endif
        @if ($role !== null)
            <span class="text-xs text-ink-muted">{{ $role }}</span>
        @endif
    </span>
</span>
