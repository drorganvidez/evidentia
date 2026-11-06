{{-- Minutes of the secretary: each row opens its edition while the meetings deadline is open, its PDF afterwards --}}
@php
    $meetingsOpen = !\Carbon\Carbon::now()->gt(\Config::meetings_timestamp() ?? now());
    $columns = [
        ['label' => 'Reunión', 'key' => 'title'],
        ['label' => 'Celebrada', 'key' => 'datetime', 'sorted' => 'desc'],
        ['label' => 'Duración', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Modificada', 'key' => 'updated_at', 'hide' => true],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
    $minutesList = $meeting_minutes->sortByDesc(fn($minutes) => $minutes->meeting?->datetime);
@endphp
<x-layout.app title="Actas" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Actas'],
]">
    <x-ui.page-header title="Actas">
        @if ($meetingsOpen)
            <x-slot:actions>
                <x-ui.button variant="primary" icon="plus" :href="route('secretary.meeting.manage.minutes.create')">Nueva
                    acta</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <x-ui.table caption="Actas" :columns="$columns" sortable :filterable="$minutesList->isNotEmpty()" count-label="actas"
        filter-placeholder="Filtrar actas">
        @forelse ($minutesList as $m)
            @php
                $pdf = route('download.minutes', ['id' => $m->id]);
                $open = $meetingsOpen ? route('secretary.meeting.manage.minutes.edit', ['id' => $m->id]) : $pdf;
            @endphp
            <x-ui.table.row :href="$open">
                <x-ui.table.cell label="Reunión" primary>
                    <a href="{{ $open }}">{{ $m->meeting->title }}</a>
                    <span class="mt-0.5 block text-sm text-ink-muted">{{ $m->meeting->place }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Celebrada" meta>
                    <x-ui.time :value="$m->meeting->datetime" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell label="Duración" numeric meta><x-ui.hours :value="$m->meeting->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Modificada" meta hide>
                    <x-ui.time :value="$m->updated_at" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell actions>
                    <span class="flex justify-end gap-0.5">
                        <x-ui.button icon-only icon="history" variant="ghost" size="sm" :label="'Historial de ' . $m->meeting->title"
                            :href="route('activity.minutes', ['id' => $m->id, 'tab' => 'historial'])" />
                        <x-ui.button icon-only icon="file-text" variant="ghost" size="sm" :label="'Descargar el PDF de ' . $m->meeting->title"
                            :href="$pdf" />
                        @if ($meetingsOpen)
                            <x-ui.button icon-only icon="pen-line" variant="ghost" size="sm" :label="'Editar ' . $m->meeting->title"
                                :href="$open" />
                            @include('meeting.partials.remove-minutes', [
                                'meeting_minutes' => $m,
                                'dialogId' => 'remove-minutes-' . $m->id,
                                'iconOnly' => true,
                            ])
                        @endif
                    </span>
                </x-ui.table.cell>
            </x-ui.table.row>
        @empty
            <x-slot:empty>
                <x-ui.empty icon="scroll-text" title="Sin actas" size="sm" :level="2" />
            </x-slot:empty>
        @endforelse
    </x-ui.table>
</x-layout.app>
