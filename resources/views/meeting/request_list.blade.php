{{-- Meeting calls of the secretary: each row opens its edition, which also downloads and removes it --}}
@php
    $columns = [
        ['label' => 'Convocatoria', 'key' => 'title'],
        ['label' => 'Programada', 'key' => 'datetime', 'sorted' => 'desc'],
        ['label' => 'Hoja de firmas', 'key' => 'sheet', 'hide' => true],
        ['label' => 'Modificada', 'key' => 'updated_at', 'hide' => true],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
    $requests = $meeting_requests->sortByDesc('datetime');
@endphp
<x-layout.app title="Convocatorias" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Convocatorias'],
]">
    <x-ui.page-header title="Convocatorias">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" :href="route('secretary.meeting.manage.request.create')">Nueva
                convocatoria</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <x-ui.table caption="Convocatorias" :columns="$columns" sortable :filterable="$requests->isNotEmpty()" count-label="convocatorias"
        filter-placeholder="Filtrar convocatorias">
        @forelse ($requests as $meeting_request)
            @php
                $edit = route('secretary.meeting.manage.request.edit', ['id' => $meeting_request->id]);
            @endphp
            <x-ui.table.row :href="$edit">
                <x-ui.table.cell label="Convocatoria" primary>
                    <a href="{{ $edit }}">{{ $meeting_request->title }}</a>
                    <span class="mt-0.5 block text-sm text-ink-muted">{{ $meeting_request->place }}</span>
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
                <x-ui.table.cell label="Modificada" meta hide>
                    <x-ui.time :value="$meeting_request->updated_at" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell tools>
                    <span class="flex justify-end gap-0.5">
                        <x-ui.button icon-only icon="history" variant="ghost" size="sm" :label="'Historial de ' . $meeting_request->title"
                            :href="route('activity.request', ['id' => $meeting_request->id, 'tab' => 'historial'])" />
                        <x-ui.button icon-only icon="file-text" variant="ghost" size="sm" :label="'Descargar el PDF de ' . $meeting_request->title"
                            :href="route('secretary.meeting.manage.request.download', [
                                'id' => $meeting_request->id,
                            ])" />
                        <x-ui.button icon-only icon="pen-line" variant="ghost" size="sm" :label="'Editar ' . $meeting_request->title"
                            :href="$edit" />
                        @include('meeting.partials.remove-request', [
                            'meeting_request' => $meeting_request,
                            'dialogId' => 'remove-request-' . $meeting_request->id,
                            'iconOnly' => true,
                        ])
                    </span>
                </x-ui.table.cell>
            </x-ui.table.row>
        @empty
            <x-slot:empty>
                <x-ui.empty icon="calendar" title="Sin convocatorias" size="sm" :level="2">
                    <x-slot:actions>
                        <x-ui.button variant="primary" icon="plus" :href="route('secretary.meeting.manage.request.create')">Nueva
                            convocatoria</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty>
            </x-slot:empty>
        @endforelse
    </x-ui.table>
</x-layout.app>
