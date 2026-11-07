{{-- Attendances of the course, from Eventbrite or recorded by hand, newest event first and in pages, with their export --}}
@php
    $closesAt = \Config::attendee_timestamp();
    $open = !\Carbon\Carbon::now()->gt($closesAt);
    $text = fn($html) => trim(html_entity_decode(strip_tags(\Html::clean((string) $html)), ENT_QUOTES | ENT_HTML5));
    $columns = $sort->columns([
        ['label' => 'Estudiante', 'key' => 'user'],
        ['label' => 'Evento', 'key' => 'event'],
        ['label' => 'Fecha', 'key' => 'start_datetime'],
        ['label' => 'Asistencia', 'key' => 'status'],
        ['label' => 'Acciones', 'srOnly' => true],
    ]);
@endphp
<x-layout.app title="Asistencias" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Asistencias']]">
    <x-ui.page-header title="Asistencias">
        @if ($closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Registro de asistencias" variant="inline" verb="para registrar asistencias"
                    :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
        <x-slot:actions>
            <x-ui.button icon="download" :href="route('registercoordinator.attendee.export')">Exportar</x-ui.button>
            @if ($open)
                <x-ui.button icon="import" :href="route('attendee.import')" id="attendee-import">Importar</x-ui.button>
                <x-ui.button variant="primary" icon="plus" :href="route('attendee.create')">Añadir
                    asistencia</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Asistencias del curso" :columns="$columns" :paginator="$attendees" filterable count-label="asistencias"
        count-singular="asistencia" filter-placeholder="Buscar asistencias">
        @if ($attendees->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="user-check" :title="$term !== null ? 'Ninguna asistencia coincide con la búsqueda' : 'No hay asistencias'" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($attendees as $attendee)
            @php
                $person = trim($attendee->user?->surname . ', ' . $attendee->user?->name, ', ');
                $event = $text($attendee->event?->name) ?: 'Sin nombre';
                $edit = $open ? route('attendee.edit', $attendee->id) : null;
            @endphp
            <x-ui.table.row :href="$edit">
                <x-ui.table.cell label="Estudiante" primary>
                    @if ($edit)
                        <a href="{{ $edit }}">{{ $person }}</a>
                    @else
                        <span class="font-semibold tracking-tight">{{ $person }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Evento">
                    @if ($attendee->event?->url)
                        <a href="{{ $attendee->event->url }}" target="_blank" rel="noopener noreferrer"
                            class="font-medium text-brand-ink">{{ $event }}</a>
                    @else
                        {{ $event }}
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Fecha" meta hide>
                    <x-ui.time :value="$attendee->event?->start_datetime" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell label="Asistencia" end>
                    <x-ui.attendee-status :status="$attendee->status" size="sm" />
                </x-ui.table.cell>
                <x-ui.table.cell actions>
                    @if ($edit)
                        <x-ui.button icon-only icon="pen-line" :label="'Editar la asistencia de ' . $person" :href="$edit" variant="ghost" />
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
