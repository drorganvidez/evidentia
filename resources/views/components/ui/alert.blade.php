{{--
    Message with a tone, its icon, an optional title, a quote, an authorship line and actions. role="note" by default;
    status and alert only for results rendered by the server (end of a signature, access error).
    <x-ui.alert tone="rejected" title="Tu coordinador necesita más pruebas" quote="Faltan las fotos del montaje.">
        <x-slot:meta><x-ui.person name="Juan" surname="Pérez" size="sm" /> <x-ui.time :value="$date" inline short /></x-slot:meta>
        <x-slot:actions><x-ui.button variant="primary">Corregir y volver a enviar</x-ui.button></x-slot:actions>
    </x-ui.alert>
    <x-ui.alert tone="accepted" role="status" title="Firma registrada" compact />
    <x-ui.alert dismissible>El plazo de subida termina el viernes.</x-ui.alert>
    dismissible runs on dismissible.js, which hands the focus to the next control when the alert closes.
--}}
@props([
    'tone' => 'notice',
    'title' => null,
    'icon' => null,
    'role' => 'note',
    'quote' => null,
    'dismissible' => false,
    'compact' => false,
])
@php
    [$toneClasses, $bubbleClasses, $toneIcon] = match ($tone) {
        'accepted' => ['border-accepted/30 bg-accepted-glass text-accepted-ink', 'bg-accepted/18', 'circle-check'],
        'pending' => ['border-pending/30 bg-pending-glass text-pending-ink', 'bg-pending/18', 'clock'],
        'rejected' => ['border-rejected/30 bg-rejected-glass text-rejected-ink', 'bg-rejected/18', 'triangle-alert'],
        'draft' => ['border-draft/30 bg-draft-glass text-draft-ink', 'bg-draft/18', 'circle-help'],
        default => ['border-notice/30 bg-notice-glass text-notice-ink', 'bg-notice/18', 'info'],
    };
    $icon ??= $toneIcon;
    $role = in_array($role, ['status', 'alert'], true) ? $role : 'note';
    $layout = $compact
        ? 'gap-3 p-4'
        : 'gap-3 p-4 md:grid-cols-[auto_minmax(0,1fr)] md:gap-x-5 md:gap-y-3 md:px-8 md:py-6';
@endphp
<div role="{{ $role }}"
    {{ $attributes->class(['grid grid-cols-[minmax(0,1fr)] rounded-xl border backdrop-blur-glass', $toneClasses, $layout]) }}
    @if ($dismissible) x-data="dismissible" x-show="open" @endif>
    @unless ($compact)
        <span aria-hidden="true"
            class="{{ Arr::toCssClasses(['grid size-11 place-items-center rounded-[0.875rem] inset-shadow-hi', $bubbleClasses]) }}">
            <x-ui.icon :name="$icon" class="size-5.5" />
        </span>
    @endunless
    <div class="flex min-w-0 items-start gap-3">
        @if ($compact)
            <x-ui.icon :name="$icon" class="mt-0.5 size-5" />
        @endif
        <div class="grid min-w-0 flex-1 gap-2">
            @if ($title !== null)
                <p class="text-base font-bold">{{ $title }}</p>
            @endif
            @if ($quote !== null)
                <blockquote class="rounded-md bg-canvas/55 px-4 py-3 text-base font-medium">«{{ $quote }}»
                </blockquote>
            @endif
            {{ $slot }}
            @isset($meta)
                <div {{ $meta->attributes->class(['flex flex-wrap items-center gap-2 text-sm opacity-95']) }}>
                    {{ $meta }}</div>
            @endisset
        </div>
        @if ($dismissible)
            <x-ui.button icon-only icon="x" variant="ghost" size="sm" label="Cerrar aviso" x-on:click="close"
                class="-mt-1 -mr-1 shrink-0" />
        @endif
    </div>
    @isset($actions)
        <div
            {{ $actions->attributes->class([
                'mt-1 flex flex-wrap items-center gap-3 max-md:*:w-full max-md:*:justify-center',
                'md:col-start-2' => !$compact,
            ]) }}>
            {{ $actions }}</div>
    @endisset
</div>
