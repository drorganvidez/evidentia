{{-- Groups of events held in parallel or of the same talk repeated: of each group a person counts only the longest event --}}
@php
    $closesAt = \Config::attendee_timestamp();
    $label = fn($event) => trim(html_entity_decode(strip_tags((string) $event->name), ENT_QUOTES | ENT_HTML5)) ?:
    'Sin nombre';
    $options = $events
        ->map(
            fn($event) => [
                'value' => $event->id,
                'label' => $label($event),
                'hint' => trim(
                    ($event->start_datetime ? \Carbon\Carbon::parse($event->start_datetime)->format('d/m/Y H:i') : '') .
                        ' · ' .
                        rtrim(rtrim(number_format((float) $event->hours, 2, ',', ''), '0'), ',') .
                        ' h' .
                        ($event->event_group !== null ? ' · ' . $event->event_group : ''),
                ),
            ],
        )
        ->values()
        ->all();
@endphp
<x-layout.app title="Grupos de eventos" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Eventos', 'href' => route('registercoordinator.event.list')],
    ['label' => 'Grupos'],
]">
    <x-ui.page-header title="Grupos de eventos"
        lede="Agrupa los eventos que se celebran a la vez o que repiten la misma charla. De cada grupo solo cuentan las horas del evento más largo al que asistió cada persona.">
        @if ($closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Registro de asistencias" variant="inline" verb="para registrar eventos"
                    :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2 lg:items-start">
        <x-ui.card title="Nuevo grupo" :level="2" id="card-new-group">
            <x-ui.form :action="route('registercoordinator.event.group.save')" id="event-group-form" :fields="['event_group' => 'Nombre del grupo', 'events' => 'Eventos']">
                <x-ui.field name="event_group" label="Nombre del grupo" required :maxlength="100" autocomplete="off"
                    hint="Si ya existe un grupo con este nombre, los eventos se añaden a él." />
                <x-ui.picker name="events[]" label="Eventos" :options="$options" placeholder="Buscar eventos"
                    hint="Elige al menos dos. Un evento que ya esté en otro grupo pasa a este." />
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" icon="layers">Agrupar</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('registercoordinator.event.list')">Volver a
                        eventos</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </x-ui.card>

        <x-ui.card title="Grupos" :level="2" id="card-groups">
            @if ($groups->isEmpty())
                <x-ui.empty icon="layers" title="No hay grupos" size="sm" :level="3"
                    text="Cada evento cuenta por separado." />
            @else
                <ul class="divide-y divide-rule" role="list">
                    @foreach ($groups as $name => $members)
                        <li class="py-4 first:pt-0 last:pb-0" aria-label="{{ 'Grupo ' . $name }}">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="font-semibold tracking-tight break-words">{{ $name }}</h3>
                                    <p class="text-sm text-ink-muted">Cuenta como máximo
                                        <x-ui.hours :value="$members->max('hours')" />
                                    </p>
                                </div>
                                <x-ui.dialog :id="'remove-group-' . $loop->index" variant="alert" title="Deshacer el grupo"
                                    :description="'Los eventos de ' . $name . ' volverán a contar por separado.'" :trigger="'Deshacer ' . $name" trigger-variant="ghost" trigger-icon="unlink"
                                    trigger-icon-only>
                                    <x-slot:footer>
                                        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                                        <form method="post"
                                            action="{{ route('registercoordinator.event.group.remove') }}">
                                            @csrf
                                            <input type="hidden" name="event_group" value="{{ $name }}">
                                            <x-ui.button variant="danger" type="submit" icon="unlink" block>Sí,
                                                deshacer</x-ui.button>
                                        </form>
                                    </x-slot:footer>
                                </x-ui.dialog>
                            </div>
                            <ul class="mt-2 space-y-1 text-sm" role="list">
                                @foreach ($members as $event)
                                    <li class="flex flex-wrap items-baseline justify-between gap-x-3">
                                        <span class="min-w-0 break-words">{{ $label($event) }}</span>
                                        <span class="text-ink-muted whitespace-nowrap">
                                            <x-ui.time :value="$event->start_datetime" inline short /> ·
                                            <x-ui.hours :value="$event->hours" />
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
</x-layout.app>
