{{--
    Every meeting of the course, for the lecturer and the president, newest first and in pages, with its minutes;
    filters by committee, dates and whether it has minutes, and a search on the server.
--}}
@use('App\Support\ListPage')
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $list = route($prefix . '.meeting.list');
    $active = count(array_filter($filters));
    $columns = [
        ['label' => 'Reunión', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Fecha', 'key' => 'datetime'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Asistentes', 'key' => 'users', 'align' => 'end'],
        ['label' => 'Acta', 'key' => 'minutes'],
    ];
@endphp
<x-layout.app title="Reuniones" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Reuniones']]">
    <x-ui.page-header title="Reuniones" />

    @include('manage.partials.meeting-tabs')

    <x-ui.filter-bar :action="$list" label="Filtros de reuniones" :open="$active > 0" :active="$active" :reset-href="ListPage::url(['comite' => null, 'desde' => null, 'hasta' => null, 'acta' => null], $list)"
        :keep="ListPage::query(['comite', 'desde', 'hasta', 'acta'])" id="meeting-filters">
        <x-ui.field name="comite" label="Comité" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$committees]" :value="(string) ($filters['comite'] ?? '')" />
        <x-ui.field name="desde" label="Desde" type="date" :value="$filters['desde']?->format('Y-m-d')" />
        <x-ui.field name="hasta" label="Hasta" type="date" :value="$filters['hasta']?->format('Y-m-d')" />
        <x-ui.field name="acta" label="Acta" type="select" :options="[
            ['value' => '', 'label' => 'Todas'],
            ['value' => 'con', 'label' => 'Con acta'],
            ['value' => 'sin', 'label' => 'Sin acta'],
        ]" :value="$filters['acta'] ?? ''" />
    </x-ui.filter-bar>

    <x-ui.table caption="Reuniones del curso" :columns="$columns" :paginator="$meetings" filterable count-label="reuniones"
        count-singular="reunión" filter-placeholder="Buscar reuniones">
        @if ($meetings->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="handshake" :title="$term !== null ? 'Ninguna reunión coincide con la búsqueda' : 'No hay reuniones'" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($meetings as $meeting)
            <x-ui.table.row>
                <x-ui.table.cell label="Reunión" primary>
                    <span class="font-semibold tracking-tight">{{ $meeting->title }}</span>
                    <span class="mt-0.5 block text-sm text-ink-muted">{{ $meeting->place }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($meeting->committee)
                        <x-ui.committee-chip :committee="$meeting->committee" :href="route($prefix . '.committee.view', ['id' => $meeting->committee->id])" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Fecha" meta><x-ui.time :value="$meeting->datetime" inline short /></x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$meeting->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Asistentes" numeric meta>{{ $meeting->users_count }}</x-ui.table.cell>
                <x-ui.table.cell label="Acta" end>
                    @if ($meeting->meetingMinutes)
                        <x-ui.button icon="file-text" size="sm" variant="ghost" :aria-label="'Acta de ' . $meeting->title"
                            :href="route('activity.minutes', ['id' => $meeting->meetingMinutes->id])">Acta</x-ui.button>
                    @else
                        <span class="text-sm text-ink-muted">Sin acta</span>
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
