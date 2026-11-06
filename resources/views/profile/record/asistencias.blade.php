{{-- Section of the record: the attendances of the person to the events, with the hours each one computes --}}
@php
    $columns = [
        ['label' => 'Evento', 'key' => 'event'],
        ['label' => 'Fecha', 'key' => 'start'],
        ['label' => 'Estado', 'key' => 'status'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
    ];
@endphp
<x-ui.table caption="Asistencias" :columns="$columns" show-caption>
    @foreach ($attendances as $attendee)
        <x-ui.table.row>
            <x-ui.table.cell label="Evento" primary><span
                    class="font-semibold">{{ $attendee->event?->name ?? '—' }}</span></x-ui.table.cell>
            <x-ui.table.cell label="Fecha" meta><x-ui.time :value="$attendee->event?->start_datetime" format="absolute"
                    pattern="d/m/Y H:i" /></x-ui.table.cell>
            <x-ui.table.cell label="Estado"><x-ui.attendee-status :status="$attendee->status" size="sm" /></x-ui.table.cell>
            <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$attendee->computedHours()" /></x-ui.table.cell>
        </x-ui.table.row>
    @endforeach
    @if ($attendances->isEmpty())
        <x-slot:empty>
            <x-ui.empty icon="ticket" title="Sin asistencias a eventos" size="sm" />
        </x-slot:empty>
    @else
        <x-slot:footer>
            <p>{{ $plural($attendances->count(), 'asistencia', 'asistencias') }} ·
                {{ $plural($totals['attendances']['attended'], 'asistida', 'asistidas') }}</p>
            <p><x-ui.hours :value="$totals['attendances']['hours']" size="sm" /> computadas</p>
        </x-slot:footer>
    @endif
</x-ui.table>
