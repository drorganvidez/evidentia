{{--
    Chip of a committee: its name after a dot in a hue decided by its id, or after its Lucide icon when the stored
    icon is a valid icon name. The Font Awesome markup still stored in committees.icon is never printed.
    <x-ui.committee-chip :committee="$evidence->committee" />
    <x-ui.committee-chip name="Logística" :id="7" icon="truck" href="/comites/7" size="md" />
--}}
@use('App\Models\Committee')
@use('BladeUI\Icons\Exceptions\SvgNotFound')
@use('BladeUI\Icons\Factory')
@props([
    'committee' => null,
    'name' => null,
    'id' => null,
    'icon' => null,
    'hue' => null,
    'href' => null,
    'size' => 'sm',
])
@php
    if ($committee instanceof Committee) {
        $name ??= $committee->name;
        $id ??= $committee->getKey();
        $icon ??= $committee->icon;
    }
    throw_if(blank($name), InvalidArgumentException::class, 'The committee chip needs a committee or a name.');
    $hue ??= (((int) $id * 7) % 12) * 30;
    // Only a plain icon name is looked up; any other value (the old Font Awesome markup) falls back to the dot
    $lucide = null;
    if (is_string($icon) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $icon) === 1) {
        try {
            app(Factory::class)->svg('lucide-' . $icon);
            $lucide = $icon;
        } catch (SvgNotFound) {
            $lucide = null;
        }
    }
    $classes = [
        'inline-flex items-center rounded-full glass-2 font-medium text-ink',
        match ($size) {
            'md' => 'gap-2 py-1.5 pr-3 pl-2.5 text-md',
            default => 'gap-1.75 py-1 pr-2.5 pl-2 text-sm',
        },
    ];
    $iconClass = $size === 'md' ? 'size-4 text-ink-muted' : 'size-3.5 text-ink-muted';
    $dotSize = $size === 'md' ? 'lg' : 'md';
@endphp
@if ($href !== null)
    <a href="{{ $href }}"
        {{ $attributes->class([...$classes, 'no-underline transition hover:text-brand-ink']) }}>
        @if ($lucide !== null)
            <x-ui.icon :name="$lucide" :class="$iconClass" />
        @else
            <x-ui.dot :hue="$hue" :size="$dotSize" />
        @endif
        {{ $name }}
    </a>
@else
    <span {{ $attributes->class($classes) }}>
        @if ($lucide !== null)
            <x-ui.icon :name="$lucide" :class="$iconClass" />
        @else
            <x-ui.dot :hue="$hue" :size="$dotSize" />
        @endif
        {{ $name }}
    </span>
@endif
