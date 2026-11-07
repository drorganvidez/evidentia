{{-- The meeting calls of every committee for the lecturer and the president, newest first, with their PDF. Read only. --}}
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $columns = [
        ['label' => 'Convocatoria', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Secretaría', 'key' => 'secretary', 'hide' => true],
        ['label' => 'Programada', 'key' => 'datetime', 'sorted' => 'desc'],
        ['label' => 'Hoja de firmas', 'key' => 'sheet', 'hide' => true],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
@endphp
<x-layout.app title="Convocatorias" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route($prefix . '.meeting.list')],
    ['label' => 'Convocatorias'],
]">
    <x-ui.page-header title="Convocatorias" />

    @include('manage.partials.meeting-tabs')

    <x-ui.table caption="Convocatorias de todos los comités" :columns="$columns" sortable :filterable="$meeting_requests->isNotEmpty()"
        count-label="convocatorias" filter-placeholder="Filtrar convocatorias">
        @if ($meeting_requests->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="calendar" title="No hay convocatorias" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($meeting_requests as $meeting_request)
            @php
                $detail = route('activity.request', ['id' => $meeting_request->id]);
                $secretary = $meeting_request->secretary?->user;
            @endphp
            <x-ui.table.row :href="$detail" :search="implode(' ', [
                $meeting_request->title,
                $meeting_request->place,
                $meeting_request->committee?->name,
                $secretary?->surname,
                $secretary?->name,
            ])">
                <x-ui.table.cell label="Convocatoria" primary>
                    <a href="{{ $detail }}">{{ $meeting_request->title }}</a>
                    <span class="mt-0.5 block text-sm text-ink-muted">{{ $meeting_request->place }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($meeting_request->committee)
                        <x-ui.committee-chip :committee="$meeting_request->committee" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Secretaría" hide>
                    @if ($secretary)
                        <x-ui.person :name="$secretary->name" :surname="$secretary->surname" size="sm" reverse :href="route('profiles.view', ['id' => $secretary->id])" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Programada" meta>
                    <x-ui.time :value="$meeting_request->datetime" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell label="Hoja de firmas" hide :sort-value="$meeting_request->signatureSheet ? 1 : 0">
                    @if ($meeting_request->signatureSheet)
                        <x-ui.badge tone="accepted" size="sm">Con hoja</x-ui.badge>
                    @else
                        <x-ui.badge tone="draft" size="sm">Sin hoja</x-ui.badge>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell tools>
                    <span class="flex justify-end gap-0.5">
                        <x-ui.button icon-only icon="history" variant="ghost" size="sm" :label="'Historial de ' . $meeting_request->title"
                            :href="route('activity.request', ['id' => $meeting_request->id, 'tab' => 'historial'])" />
                        <x-ui.button icon-only icon="file-text" variant="ghost" size="sm" :label="'Descargar el PDF de ' . $meeting_request->title"
                            :href="route('download.request', ['id' => $meeting_request->id])" />
                    </span>
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
