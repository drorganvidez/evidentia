{{--
    One step (parent variant steps) or one event (parent variant log) of x-ui.timeline. In steps the node and the
    connector follow state, and every state adds a hidden text so the colour never stands alone; error is the
    current step with an incident. In log the dot takes tone and the slot continues the title.
    <x-ui.timeline.item state="done" title="Datos de la reunión" href="..." hint="Completado" />
    <x-ui.timeline.item state="error" title="Rechazada" :at="$at" />
    <x-ui.timeline.item tone="rejected" title="Rechazada" :at="$at">por <a href="...">Carlos Navarro</a></x-ui.timeline.item>
    The variant is read from the parent with @aware; both defaults are log, so an item outside a list is an event.
--}}
@aware(['variant' => 'log'])
@props(['state' => 'upcoming', 'tone' => null, 'title' => null, 'at' => null, 'href' => null, 'hint' => null])
@php
    throw_if(blank($title), InvalidArgumentException::class, 'The timeline item needs a title.');
    [$node, $connector, $colour, $stateText] = match ($state) {
        'done' => [
            'bg-aurora-500 ring-3 ring-aurora-500/25 shadow-glow-sm',
            'before:bg-linear-to-r before:from-aurora-500 before:to-cyan-400',
            'text-ink',
            'completado',
        ],
        'current' => [
            'bg-aurora-500 ring-3 ring-aurora-500/25 shadow-glow-sm',
            'before:bg-linear-to-r before:from-aurora-500 before:to-cyan-400',
            'text-ink',
            'paso actual',
        ],
        'error' => [
            'bg-rejected ring-3 ring-rejected/25 shadow-glow-sm',
            'before:bg-rule',
            'text-rejected-ink',
            'con incidencia, paso actual',
        ],
        default => ['bg-rule', 'before:bg-rule', 'text-ink-muted', 'pendiente'],
    };
    $current = in_array($state, ['current', 'error'], true);
@endphp
@if ($variant === 'steps')
    <li @if ($current) aria-current="step" @endif
        {{ $attributes->class([
            'relative grid gap-0.5 pb-4 pl-7 text-sm last:pb-0 before:absolute before:top-4 before:-bottom-1 before:left-1.25 before:w-0.5 before:rounded-full last:before:hidden md:gap-1.5 md:pt-4.5 md:pb-0 md:pl-0 md:before:top-1.25 md:before:-right-3 md:before:bottom-auto md:before:left-3.5 md:before:h-0.5 md:before:w-auto md:last:before:right-1/2 md:last:before:block',
            $connector,
        ]) }}>
        <span aria-hidden="true"
            class="absolute top-0.75 left-0 size-3 rounded-full border-2 border-glass-2 md:top-0 {{ $node }}"></span>
        <span class="font-semibold {{ $colour }}">
            @if ($href !== null)
                <a href="{{ $href }}"
                    class="text-inherit no-underline hover:text-brand-ink">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
            <span class="sr-only">, {{ $stateText }}</span>
        </span>
        @if ($hint !== null)
            <span class="text-xs text-ink-muted">{{ $hint }}</span>
        @elseif ($at !== null)
            <x-ui.time :value="$at" format="absolute" pattern="d/m H:i" class="text-xs text-ink-muted" />
        @endif
    </li>
@else
    <li {{ $attributes->class(['grid grid-cols-[1.25rem_minmax(0,1fr)] gap-3 py-2 text-sm']) }}>
        <x-ui.dot :tone="$tone ?? 'draft'" class="mt-1.25 justify-self-center" />
        <div class="min-w-0">
            <strong class="font-semibold">{{ $title }}</strong>
            {{ $slot }}
            @if ($hint !== null)
                <span class="block text-xs text-ink-muted">{{ $hint }}</span>
            @elseif ($at !== null)
                <x-ui.time :value="$at" format="absolute" class="block text-xs text-ink-muted" />
            @endif
        </div>
    </li>
@endif
