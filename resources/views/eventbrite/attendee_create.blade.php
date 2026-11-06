{{-- A new attendance recorded by hand; ?event= preselects the event --}}
<x-layout.app title="Añadir asistencia" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Asistencias', 'href' => route('registercoordinator.attendee.list')],
    ['label' => 'Añadir asistencia'],
]">
    <x-ui.page-header title="Añadir asistencia" />

    <x-ui.card title="Datos de la asistencia" :level="2" class="max-w-2xl">
        @include('eventbrite.partials.attendee-form', [
            'action' => route('attendee.store'),
            'attendee' => null,
            'event' => request('event'),
            'submit' => 'Crear asistencia',
        ])
    </x-ui.card>
</x-layout.app>
