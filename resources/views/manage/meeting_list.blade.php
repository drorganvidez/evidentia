{{-- Every meeting of the course, for the lecturer and the president, newest first, with the PDF of its minutes --}}
@php
    $columns = [
        ['label' => 'Reunión', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Fecha', 'key' => 'datetime'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Asistentes', 'key' => 'users', 'align' => 'end'],
        ['label' => 'Acta', 'key' => 'minutes'],
    ];
    $rows = $meetings->sortByDesc('datetime')->values();
@endphp
<x-layout.app title="Reuniones" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Reuniones']]">
    <x-ui.page-header title="Reuniones" />

    <x-ui.table caption="Reuniones del curso" :columns="$columns" :filterable="$rows->isNotEmpty()" count-label="reuniones"
        filter-placeholder="Filtrar reuniones">
        @if ($rows->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="handshake" title="No hay reuniones" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($rows as $meeting)
            <x-ui.table.row :search="implode(' ', [$meeting->title, $meeting->place, $meeting->committee?->name])">
                <x-ui.table.cell label="Reunión" primary>
                    <span class="font-semibold tracking-tight">{{ $meeting->title }}</span>
                    <span class="mt-0.5 block text-sm text-ink-muted">{{ $meeting->place }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($meeting->committee)
                        <x-ui.committee-chip :committee="$meeting->committee" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Fecha" meta><x-ui.time :value="$meeting->datetime" inline short /></x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$meeting->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Asistentes" numeric meta>{{ $meeting->users->count() }}</x-ui.table.cell>
                <x-ui.table.cell label="Acta" end>
                    @if ($meeting->meetingMinutes)
                        <x-ui.button icon="file-text" size="sm" variant="ghost" :aria-label="'Acta de ' . $meeting->title"
                            :href="route('download.minutes', ['id' => $meeting->meetingMinutes->id])">Acta</x-ui.button>
                    @else
                        <span class="text-sm text-ink-muted">Sin acta</span>
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
