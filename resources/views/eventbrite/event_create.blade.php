{{-- An event created by hand (sessions held before the course used Eventbrite), new or edited --}}
@php
    $title = $event->exists ? 'Editar evento' : 'Nuevo evento';
    $start = $event->start_datetime ? \Carbon\Carbon::parse($event->start_datetime)->format('Y-m-d\TH:i') : null;
    $end = $event->end_datetime ? \Carbon\Carbon::parse($event->end_datetime)->format('Y-m-d\TH:i') : null;
    $closesAt = \Config::attendee_timestamp();
@endphp
<x-layout.app :title="$title" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Eventos', 'href' => route('registercoordinator.event.list')],
    ['label' => $title],
]">
    <x-ui.page-header :title="$title">
        @if ($closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Registro de asistencias" variant="inline" verb="para registrar eventos"
                    :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
    </x-ui.page-header>

    <x-ui.card title="Datos del evento" :level="2" class="max-w-2xl">
        <x-ui.form :action="$event->exists
            ? route('registercoordinator.event.update', $event->id)
            : route('registercoordinator.event.store')" id="event-form" :fields="[
            'name' => 'Nombre',
            'start_datetime' => 'Inicio',
            'end_datetime' => 'Fin',
            'capacity' => 'Capacidad',
            'description' => 'Descripción',
        ]">
            <x-ui.field name="name" label="Nombre" :value="$event->name" required :maxlength="255" autocomplete="off" />
            <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
                <x-ui.field name="start_datetime" label="Inicio" type="datetime-local" :value="$start" required />
                <x-ui.field name="end_datetime" label="Fin" type="datetime-local" :value="$end" required />
            </div>
            <x-ui.field name="capacity" label="Capacidad" type="number" :value="$event->capacity" min="0"
                inputmode="numeric" class="md:max-w-64" />
            <x-ui.field name="description" label="Descripción" type="textarea" :value="$event->description" :rows="4"
                :maxlength="5000" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary"
                    icon="save">{{ $event->exists ? 'Guardar evento' : 'Crear evento' }}</x-ui.button>
                <x-ui.button variant="ghost" :href="route('registercoordinator.event.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
