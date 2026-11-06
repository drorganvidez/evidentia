{{-- The three status mappers with every stored value they understand, plus an unknown one --}}
@php
    $columns = [['label' => 'Valor guardado', 'key' => 'value'], ['label' => 'Etiqueta', 'key' => 'label']];
    $evidences = ['DRAFT', 'PENDING', 'ACCEPTED', 'REJECTED', 'BIN', 'OTRO'];
    $attendees = [
        'Attending',
        'Checked In',
        'Guests Attended',
        'Guests Attending',
        'Not Attending (Refunded/Canceled)',
        'Not Attending',
        'Transferred',
    ];
    $events = ['draft', 'live', 'started', 'ended', 'completed', 'canceled', 'postponed'];
@endphp
<section id="ui-evidence-status" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-evidence-status-title">
    <h2 id="ui-evidence-status-title" class="text-2xl font-bold tracking-tight">Estados</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <h3 class="text-lg font-bold tracking-tight">Evidencias</h3>
            <x-ui.table caption="Estados de evidencia" :columns="$columns" density="compact" :cards="false">
                @foreach ($evidences as $value)
                    <x-ui.table.row>
                        <x-ui.table.cell header class="whitespace-nowrap">
                            <x-ui.badge variant="chip" mono>{{ $value }}</x-ui.badge>
                        </x-ui.table.cell>
                        <x-ui.table.cell>
                            <x-ui.evidence-status :status="$value" size="sm" :reason="$value === 'REJECTED' ? 'Faltan las pruebas del montaje' : null" />
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table>
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <h3 class="text-lg font-bold tracking-tight">Asistentes</h3>
            <x-ui.table caption="Estados de asistencia" :columns="$columns" density="compact" :cards="false">
                @foreach ($attendees as $value)
                    <x-ui.table.row>
                        <x-ui.table.cell header class="whitespace-nowrap">
                            <x-ui.badge variant="chip" mono>{{ $value }}</x-ui.badge>
                        </x-ui.table.cell>
                        <x-ui.table.cell><x-ui.attendee-status :status="$value" size="sm" /></x-ui.table.cell>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table>
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <h3 class="text-lg font-bold tracking-tight">Eventos</h3>
            <x-ui.table caption="Estados de evento" :columns="$columns" density="compact" :cards="false">
                @foreach ($events as $value)
                    <x-ui.table.row>
                        <x-ui.table.cell header class="whitespace-nowrap">
                            <x-ui.badge variant="chip" mono>{{ $value }}</x-ui.badge>
                        </x-ui.table.cell>
                        <x-ui.table.cell><x-ui.event-status :status="$value" size="sm" /></x-ui.table.cell>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table>
        </div>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
        <h3 class="text-lg font-bold tracking-tight">Tamaños</h3>
        <ul class="flex flex-wrap items-center gap-3">
            <li><x-ui.evidence-status status="PENDING" size="sm" /></li>
            <li><x-ui.evidence-status status="PENDING" /></li>
            <li><x-ui.evidence-status status="PENDING" size="lg" /></li>
            <li><x-ui.attendee-status status="Checked In" /></li>
            <li><x-ui.event-status status="live" /></li>
        </ul>
    </div>
</section>
