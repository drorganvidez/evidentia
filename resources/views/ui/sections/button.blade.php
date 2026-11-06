{{-- Every variant and size of the button, the icon placements, the states and the clipboard behaviour --}}
@php
    $variants = [
        'primary' => 'Nueva evidencia',
        'quiet' => 'Cancelar',
        'danger' => 'Eliminar',
        'ghost' => 'Ver todas',
    ];
    $sizes = ['sm', 'md', 'lg'];
@endphp
<section id="ui-button" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-button-title">
    <h2 id="ui-button-title" class="text-2xl font-bold tracking-tight">Botones</h2>

    <div class="grid gap-4">
        @foreach ($variants as $variant => $text)
            <div class="flex flex-wrap items-center gap-3">
                <span class="w-20 font-mono text-xs text-ink-muted">{{ $variant }}</span>
                @foreach ($sizes as $size)
                    <x-ui.button :variant="$variant" :size="$size">{{ $text }}</x-ui.button>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="grid gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">icon</span>
            <x-ui.button variant="primary" icon="plus">Nueva evidencia</x-ui.button>
            <x-ui.button icon="arrow-right" icon-end>Siguiente</x-ui.button>
            <x-ui.button variant="danger" icon="trash-2">Eliminar</x-ui.button>
            <x-ui.button variant="ghost" icon="download" size="sm">Descargar</x-ui.button>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">icon-only</span>
            @foreach ($sizes as $size)
                <x-ui.button icon-only icon="eye" label="Abrir Montaje del photocall" :size="$size" />
            @endforeach
            <x-ui.button icon-only variant="primary" icon="plus" label="Nueva evidencia" />
            <x-ui.button icon-only variant="danger" icon="trash-2" label="Eliminar la evidencia" />
            <x-ui.button icon-only variant="ghost" icon="pencil" label="Editar" />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">href</span>
            <x-ui.button href="#ui-button" variant="primary" icon="plus">Nueva evidencia</x-ui.button>
            <x-ui.button href="#ui-button" icon="arrow-right" icon-end>Ver el detalle</x-ui.button>
            <x-ui.button href="#ui-button" icon-only icon="eye" label="Abrir Montaje del photocall" />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">submit</span>
            <form method="get" action="{{ route('ui.index') }}#ui-button" class="contents">
                <x-ui.button type="submit" variant="primary">Guardar</x-ui.button>
                <x-ui.button type="reset">Restablecer</x-ui.button>
            </form>
        </div>
    </div>

    <div class="grid gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">disabled</span>
            <x-ui.button variant="primary" disabled>Guardar</x-ui.button>
            <x-ui.button disabled>Cancelar</x-ui.button>
            <x-ui.button variant="danger" disabled>Eliminar</x-ui.button>
            <x-ui.button icon-only icon="eye" label="Abrir Montaje del photocall" disabled />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">busy</span>
            <x-ui.button variant="primary" busy>Guardar</x-ui.button>
            <x-ui.button icon="download" busy>Descargar</x-ui.button>
            <x-ui.button icon="arrow-right" icon-end busy>Siguiente</x-ui.button>
            <x-ui.button icon-only icon="refresh-cw" label="Actualizar" busy />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">pressed</span>
            <x-ui.button variant="ghost" icon="star" :pressed="true">Favorita</x-ui.button>
            <x-ui.button variant="ghost" icon="star" :pressed="false">Favorita</x-ui.button>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">copy</span>
            <x-ui.button icon="copy" copy="https://evidentia.us.es/sign/4821">Copiar enlace</x-ui.button>
            <x-ui.button icon-only icon="copy" label="Copiar el identificador" copy="EV-0033" size="sm" />
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="w-20 font-mono text-xs text-ink-muted">dim</span>
            <div class="group glass-2 flex items-center gap-1 rounded-full p-1">
                <x-ui.button icon-only variant="ghost" size="sm" icon="eye" label="Abrir Montaje del photocall"
                    dim />
                <x-ui.button icon-only variant="ghost" size="sm" icon="pencil"
                    label="Editar Montaje del photocall" dim />
                <x-ui.button icon-only variant="danger" size="sm" icon="trash-2"
                    label="Eliminar Montaje del photocall" dim />
            </div>
        </div>
    </div>

    <div class="grid gap-3 sm:max-w-sm">
        <span class="font-mono text-xs text-ink-muted">block</span>
        <x-ui.button variant="primary" size="lg" block>Entrar</x-ui.button>
        <x-ui.button size="lg" block icon="arrow-left">Volver</x-ui.button>
    </div>
</section>
