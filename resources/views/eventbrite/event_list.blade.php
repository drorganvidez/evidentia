{{-- Events of the course, from Eventbrite or created by hand, newest first and in pages, with their attendances and visibility --}}
@php
    $closesAt = \Config::attendee_timestamp();
    $open = !\Carbon\Carbon::now()->gt($closesAt);
    $text = fn($html) => trim(html_entity_decode(strip_tags(\Html::clean((string) $html)), ENT_QUOTES | ENT_HTML5));
    $columns = $sort->columns([
        ['label' => 'Evento', 'key' => 'name'],
        ['label' => 'Fecha', 'key' => 'start_datetime'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Capacidad', 'key' => 'capacity', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
        ['label' => 'Acciones', 'srOnly' => true],
    ]);
@endphp
<x-layout.app title="Eventos" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Eventos']]">
    <x-ui.page-header title="Eventos">
        @if ($closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Registro de asistencias" variant="inline" verb="para registrar eventos"
                    :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
        @if ($open)
            <x-slot:actions>
                <x-ui.button icon="layers" :href="route('registercoordinator.event.groups')">Grupos</x-ui.button>
                <x-ui.button icon="rotate-ccw" :href="route('registercoordinator.event.load')">Cargar desde
                    Eventbrite</x-ui.button>
                <x-ui.button variant="primary" icon="plus" :href="route('registercoordinator.event.create')">Nuevo
                    evento</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.table caption="Eventos del curso" :columns="$columns" :paginator="$events" filterable count-label="eventos"
        count-singular="evento" filter-placeholder="Buscar eventos">
        @if ($events->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="calendar" :title="$term !== null ? 'Ningún evento coincide con la búsqueda' : 'No hay eventos'" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($events as $event)
            @php
                $name = $text($event->name) ?: 'Sin nombre';
                $manual = $event->id_eventbrite === null;
            @endphp
            <x-ui.table.row>
                <x-ui.table.cell label="Evento" primary>
                    @if ($event->url)
                        <a href="{{ $event->url }}" target="_blank" rel="noopener noreferrer">{{ $name }}</a>
                    @else
                        <span class="font-semibold tracking-tight">{{ $name }}</span>
                    @endif
                    @if ($manual || $event->hidden || $event->event_group !== null)
                        <span class="mt-1 flex flex-wrap items-center gap-1.5">
                            @if ($manual)
                                <x-ui.badge variant="chip" size="sm">Creado a mano</x-ui.badge>
                            @endif
                            @if ($event->hidden)
                                <x-ui.badge tone="draft" size="sm">Oculto</x-ui.badge>
                            @endif
                            @if ($event->event_group !== null)
                                <x-ui.badge tone="notice"
                                    size="sm">{{ 'Grupo ' . $event->event_group }}</x-ui.badge>
                            @endif
                        </span>
                    @endif
                    @if (filled($text($event->description)))
                        <span class="mt-0.5 block max-w-[40ch] truncate text-sm text-ink-muted">
                            {{ Str::limit($text($event->description), 120) }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Fecha" meta>
                    <x-ui.time :value="$event->start_datetime" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$event->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Capacidad" numeric meta hide>{{ $event->capacity ?? '—' }}</x-ui.table.cell>
                <x-ui.table.cell label="Estado" end><x-ui.event-status :status="$event->status"
                        size="sm" /></x-ui.table.cell>
                <x-ui.table.cell tools>
                    @if ($open)
                        <div class="flex items-center justify-end gap-1">
                            @if (!$manual)
                                <x-ui.button icon-only icon="download" :label="'Cargar las asistencias de ' . $name" variant="ghost"
                                    :href="route('registercoordinator.attendee.load', [
                                        'id' => $event->id_eventbrite,
                                    ])" />
                            @else
                                <x-ui.button icon-only icon="user-plus" :label="'Añadir una asistencia a ' . $name" variant="ghost"
                                    :href="route('attendee.create', ['event' => $event->id])" />
                                <x-ui.button icon-only icon="pen-line" :label="'Editar ' . $name" variant="ghost"
                                    :href="route('registercoordinator.event.edit', $event->id)" />
                            @endif
                            <form method="post"
                                action="{{ route($event->hidden ? 'registercoordinator.event.unhide' : 'registercoordinator.event.hide', ['id' => $event->id]) }}">
                                @csrf
                                <x-ui.button type="submit" icon-only :icon="$event->hidden ? 'eye' : 'eye-off'" :label="($event->hidden ? 'Mostrar ' : 'Ocultar ') . $name"
                                    variant="ghost" />
                            </form>
                            @if ($manual)
                                <x-ui.dialog :id="'remove-event-' . $event->id" variant="alert" title="Borrar el evento"
                                    :description="'Se borrará ' . $name . '. Esta acción no se puede deshacer.'" :trigger="'Borrar ' . $name" trigger-variant="ghost" trigger-icon="trash-2"
                                    trigger-icon-only>
                                    <x-slot:footer>
                                        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                                        <form method="post"
                                            action="{{ route('registercoordinator.event.destroy', $event->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí,
                                                borrar</x-ui.button>
                                        </form>
                                    </x-slot:footer>
                                </x-ui.dialog>
                            @endif
                        </div>
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
