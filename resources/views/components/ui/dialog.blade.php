{{--
    Native <dialog> with its trigger: a modal, an alert that asks for confirmation, or the drawer of the phone.
    <x-ui.dialog id="reject-33" title="Rechazar la evidencia" trigger="Rechazar" trigger-icon="circle-x">
        ...body...
        <x-slot:footer><x-ui.button x-on:click="close">Cancelar</x-ui.button>...</x-slot:footer>
    </x-ui.dialog>
    <x-ui.dialog id="remove-33" variant="alert" title="Eliminar la evidencia" description="Se borrarán también sus pruebas."
        trigger="Eliminar" trigger-icon="trash-2" trigger-icon-only>
        <x-slot:footer>
            <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
            <form method="post" action="...">@csrf<x-ui.button variant="danger" type="submit">Sí, eliminar</x-ui.button></form>
        </x-slot:footer>
    </x-ui.dialog>
    A trigger slot replaces the button; it must carry x-on:click="show", aria-haspopup="dialog" and aria-controls.
    Below md the modal and the alert are a bottom sheet (sheet); open-on-load reopens it after a validation error.
--}}
@use('Illuminate\View\ComponentSlot')
@props([
    'id' => null,
    'title' => null,
    'description' => null,
    'variant' => 'modal',
    'size' => 'md',
    'sheet' => true,
    'trigger' => null,
    'triggerVariant' => null,
    'triggerIcon' => null,
    'triggerIconOnly' => false,
    'closeLabel' => 'Cerrar',
    'openOnLoad' => false,
])
@php
    throw_if(blank($id), InvalidArgumentException::class, 'The dialog needs an id.');
    throw_if(blank($title), InvalidArgumentException::class, 'The dialog needs a title.');
    $isAlert = $variant === 'alert';
    $isDrawer = $variant === 'drawer';
    throw_if($isAlert && blank($description), InvalidArgumentException::class, 'An alert dialog needs a description.');
    // The trigger is a prop (the text of the generated button) or a slot that replaces it
    $customTrigger = $trigger instanceof ComponentSlot ? $trigger : null;
    $triggerText = is_string($trigger) && $trigger !== '' ? $trigger : null;
    $triggerVariant ??= $isAlert ? 'danger' : 'quiet';
    $sizeClass = match ($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        default => 'max-w-lg',
    };
    $dialogClasses = [
        'glass content-start overflow-y-auto p-0 text-ink transition transition-discrete backdrop:bg-slate-950/40 backdrop:backdrop-blur-xs open:grid starting:open:opacity-0',
        'm-auto w-[calc(100%-2rem)] max-h-[calc(100dvh-2rem)] rounded-xl starting:open:translate-y-2' => !$isDrawer,
        $sizeClass => !$isDrawer,
        'max-md:mt-auto max-md:mb-0 max-md:max-h-[90dvh] max-md:w-full max-md:max-w-none max-md:rounded-b-none max-md:before:mx-auto max-md:before:mt-2 max-md:before:block max-md:before:h-1 max-md:before:w-10 max-md:before:rounded-full max-md:before:bg-rule' =>
            !$isDrawer && $sheet,
        'm-0 h-dvh max-h-none w-72 max-w-[85vw] rounded-l-none rounded-r-xl starting:open:-translate-x-full' => $isDrawer,
    ];
@endphp
<div x-data="dialog" @if ($openOnLoad) data-open @endif {{ $attributes }}>
    @if ($customTrigger !== null)
        {{ $customTrigger }}
    @elseif ($triggerText !== null)
        <x-ui.button :variant="$triggerVariant" :icon="$triggerIcon" :icon-only="$triggerIconOnly" :label="$triggerText" aria-haspopup="dialog"
            aria-controls="{{ $id }}" aria-expanded="false" x-bind:aria-expanded="open ? 'true' : 'false'"
            x-on:click="show">{{ $triggerText }}</x-ui.button>
    @endif
    <dialog id="{{ $id }}" @if ($isAlert) role="alertdialog" @endif
        aria-labelledby="{{ $id }}-title"
        @if ($description !== null) aria-describedby="{{ $id }}-desc" @endif x-ref="dialog"
        x-on:close="onClose" x-on:click="onBackdropClick" closedby="any" @class($dialogClasses)>
        <div @class([
            'flex items-start justify-between gap-4',
            $isDrawer ? 'px-4 pt-4' : 'p-6 pb-0',
        ])>
            <h2 id="{{ $id }}-title" tabindex="-1" class="text-xl font-bold tracking-tight">
                {{ $title }}</h2>
            @unless ($isAlert)
                <x-ui.button icon-only icon="x" :label="$closeLabel" variant="ghost" size="sm" x-on:click="close"
                    class="-mt-1 -mr-2 shrink-0" />
            @endunless
        </div>
        @if ($description !== null)
            <p id="{{ $id }}-desc" @class([
                'text-md text-ink-muted',
                $isDrawer ? 'px-4 pt-1' : 'px-6 pt-2',
                'pb-6' => $slot->isEmpty() && !$isDrawer,
            ])>
                {{ $description }}</p>
        @endif
        @unless ($slot->isEmpty())
            <div @class([$isDrawer ? 'px-3 py-4' : 'p-6'])>{{ $slot }}</div>
        @endunless
        @isset($footer)
            <div
                {{ $footer->attributes->class(['flex flex-col-reverse gap-3 border-t border-rule p-6 md:flex-row md:justify-end']) }}>
                {{ $footer }}</div>
        @endisset
    </dialog>
</div>
