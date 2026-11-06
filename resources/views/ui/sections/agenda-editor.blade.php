{{-- Agenda editor: a request with three points and the live value of its hidden input, an empty one and one with a server error --}}
@php
    $points = ['Lectura y aprobación del acta anterior', 'Presupuesto del photocall', 'Calendario de montaje'];
@endphp
<section id="ui-agenda-editor" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-agenda-editor-title">
    <h2 id="ui-agenda-editor-title" class="text-2xl font-bold tracking-tight">Orden del día</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
        <x-ui.card title="Convocatoria con tres puntos" :level="3">
            <x-ui.agenda-editor id="agenda-filled" :points="$points"
                hint="Mueve los puntos con los botones o con Alt y las flechas." data-demo="filled">
                <div class="grid grid-cols-[minmax(0,1fr)] gap-1 border-t border-rule pt-3">
                    <span class="text-xs font-semibold text-ink-muted">Valor del campo oculto</span>
                    <output class="font-mono text-xs break-all text-ink-muted" x-text="json"></output>
                </div>
            </x-ui.agenda-editor>
        </x-ui.card>

        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Sin puntos" :level="3">
                <x-ui.agenda-editor id="agenda-empty" name="points_empty" :max="3" data-demo="empty" />
            </x-ui.card>
            <x-ui.card title="Con error del servidor" :level="3">
                <x-ui.agenda-editor id="agenda-error" name="points_error" :points="['Presupuesto del photocall']"
                    error="El orden del día no es válido." data-demo="error" />
            </x-ui.card>
        </div>
    </div>
</section>
