{{--
    Every attendance of the course for the lecturer and the president, newest event first and in pages, filtered by
    event and state and searched on the server, with the hours each one counts, and the events with their attendees.
    Read only.
--}}
@use('App\Support\ListPage')
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $list = route($prefix . '.attendee.list');
    $text = fn($html) => trim(html_entity_decode(strip_tags(\Html::clean((string) $html)), ENT_QUOTES | ENT_HTML5));
    $eventName = fn($event) => $text($event?->name) ?: 'Sin nombre';
    $date = fn($value) => filled($value) ? \Carbon\Carbon::parse($value)->format('d/m/Y') : null;
    // Every tab keeps the event, the search and the rows per page, back on the first page
    $tabItems = array_map(
        fn(array $tab): array => [
            'label' => $tab[0],
            'href' => ListPage::url(['estado' => $tab[1]], $list),
            'count' => $counts->get($tab[1] ?? 'todas', 0),
            'current' => $state === $tab[1],
        ],
        [['Todas', null], ['Asistidas', 'asistidas'], ['Pendientes', 'pendientes'], ['No asistidas', 'no-asistidas']],
    );
    $eventOptions = [
        ['value' => '', 'label' => 'Todos'],
        ...$events
            ->map(
                fn($item) => [
                    'value' => $item->id,
                    'label' => trim($eventName($item) . ' · ' . $date($item->start_datetime), ' ·'),
                ],
            )
            ->all(),
    ];
    $empty = match ($term !== null ? 'busqueda' : $state) {
        'busqueda' => 'Ninguna asistencia coincide con la búsqueda',
        'asistidas' => 'No hay asistencias registradas',
        'pendientes' => 'No hay asistencias pendientes',
        'no-asistidas' => 'No hay ausencias',
        default => 'No hay asistencias',
    };
    $columns = [
        ['label' => 'Persona', 'key' => 'user'],
        ['label' => 'Evento', 'key' => 'event'],
        ['label' => 'Fecha', 'key' => 'start_datetime'],
        ['label' => 'Asistencia', 'key' => 'status'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
    ];
    $eventColumns = [
        ['label' => 'Evento', 'key' => 'name'],
        ['label' => 'Fecha', 'key' => 'start_datetime'],
        ['label' => 'Inscritos', 'key' => 'attendees', 'align' => 'end'],
        ['label' => 'Asistentes', 'key' => 'attended', 'align' => 'end'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Horas computadas', 'key' => 'computed', 'align' => 'end'],
    ];
@endphp
<x-layout.app title="Asistencias" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Asistencias']]">
    <x-ui.page-header title="Asistencias" />

    <x-ui.filter-bar :action="$list" label="Filtros de las asistencias" :open="$event !== null" :active="$event ? 1 : 0"
        :reset-href="ListPage::url(['evento' => null], $list)" :keep="ListPage::query(['evento'])" id="attendee-filters">
        <x-ui.field name="evento" label="Evento" type="select" :options="$eventOptions" :value="$event?->id ?? ''" />
    </x-ui.filter-bar>

    <x-ui.table id="attendances" caption="Asistencias del curso" :columns="$columns" :paginator="$attendees" filterable
        count-label="asistencias" count-singular="asistencia" filter-placeholder="Buscar asistencias">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar asistencias por estado" :items="$tabItems" />
        </x-slot:toolbar>
        @if ($attendees->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="user-check" :title="$empty" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($attendees as $attendee)
            @php
                $person = $attendee->user;
                $name = $eventName($attendee->event);
            @endphp
            <x-ui.table.row>
                <x-ui.table.cell label="Persona" primary>
                    <x-ui.person :name="$person->name" :surname="$person->surname" size="sm" reverse :href="route('profiles.view', ['id' => $person->id])" />
                </x-ui.table.cell>
                <x-ui.table.cell label="Evento">
                    @if ($attendee->event?->url)
                        <a href="{{ $attendee->event->url }}" target="_blank" rel="noopener noreferrer"
                            class="font-medium text-brand-ink">{{ $name }}<span class="sr-only">(se abre en una
                                pestaña nueva)</span></a>
                    @else
                        {{ $name }}
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Fecha" meta>
                    <x-ui.time :value="$attendee->event?->start_datetime" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell label="Asistencia">
                    <x-ui.attendee-status :status="$attendee->status" size="sm" />
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$attendee->computedHours()" /></x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($attendees->isNotEmpty())
            <x-slot:footer>
                <span>{{ $attendees->total() }} {{ $attendees->total() === 1 ? 'asistencia' : 'asistencias' }} ·
                    <x-ui.hours :value="$hours" size="sm" class="text-ink" /></span>
            </x-slot:footer>
        @endif
    </x-ui.table>

    <x-ui.table id="events" caption="Eventos" show-caption :columns="$eventColumns" :filterable="$events->isNotEmpty()"
        count-label="eventos" filter-placeholder="Filtrar eventos">
        @if ($events->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="calendar" title="No hay eventos" size="sm" :level="3" />
            </x-slot:empty>
        @endif
        @foreach ($events as $item)
            @php
                $name = $eventName($item);
                $href = $list . '?' . http_build_query(['evento' => $item->id]);
            @endphp
            <x-ui.table.row :href="$href" :search="$name">
                <x-ui.table.cell label="Evento" primary>
                    <a href="{{ $href }}">{{ $name }}</a>
                    @if ($item->hidden)
                        <span class="mt-1 flex"><x-ui.badge tone="draft" size="sm">Oculto</x-ui.badge></span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Fecha" meta>
                    <x-ui.time :value="$item->start_datetime" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell label="Inscritos" numeric meta>{{ $item->attendees_count }}</x-ui.table.cell>
                <x-ui.table.cell label="Asistentes" numeric meta>{{ $item->attended_count }}</x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$item->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Horas computadas" numeric meta><x-ui.hours
                        :value="$item->attended_count * (float) $item->hours" /></x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
