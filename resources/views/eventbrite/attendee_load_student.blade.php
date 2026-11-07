{{-- Brings from Eventbrite only the attendances of one student, going through every event of the course --}}
@php
    $people = $users
        ->map(fn($user) => ['value' => (string) $user->id, 'label' => trim($user->surname . ', ' . $user->name, ', ')])
        ->values()
        ->all();
@endphp
<x-layout.app title="Cargar asistencias de un estudiante" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Asistencias', 'href' => route('registercoordinator.attendee.list')],
    ['label' => 'Cargar de un estudiante'],
]">
    <x-ui.page-header title="Cargar asistencias de un estudiante"
        lede="Consulta en Eventbrite todos los eventos del curso y guarda solo las asistencias de la persona elegida. Puede tardar si hay muchos eventos." />

    <x-ui.card title="Estudiante" :level="2" class="max-w-2xl">
        <x-ui.form :action="route('registercoordinator.attendee.load.student.save')" id="attendee-load-student-form" :fields="['user_id' => 'Estudiante']">
            <x-ui.field name="user_id" label="Estudiante" type="select" :options="$people" required />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="download">Cargar asistencias</x-ui.button>
                <x-ui.button variant="ghost" :href="route('registercoordinator.attendee.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
