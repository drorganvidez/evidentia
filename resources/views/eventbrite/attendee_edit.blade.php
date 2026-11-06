{{-- An attendance recorded by hand or loaded from Eventbrite: change it or delete it --}}
@php
    $person = trim($attendee->user?->surname . ', ' . $attendee->user?->name, ', ');
@endphp
<x-layout.app title="Editar asistencia" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Asistencias', 'href' => route('registercoordinator.attendee.list')],
    ['label' => 'Editar asistencia'],
]">
    <x-ui.page-header title="Editar asistencia">
        <x-slot:actions>
            <x-ui.dialog id="remove-attendee" variant="alert" title="Eliminar la asistencia" :description="'Se eliminará la asistencia de ' . $person . '. Esta acción no se puede deshacer.'"
                trigger="Eliminar" trigger-icon="trash-2">
                <x-slot:footer>
                    <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                    <form method="post" action="{{ route('attendee.destroy', $attendee->id) }}">
                        @csrf
                        @method('DELETE')
                        <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
                    </form>
                </x-slot:footer>
            </x-ui.dialog>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Datos de la asistencia" :level="2" class="max-w-2xl">
        @include('eventbrite.partials.attendee-form', [
            'action' => route('attendee.update', $attendee->id),
            'attendee' => $attendee,
            'event' => null,
            'submit' => 'Guardar cambios',
        ])
    </x-ui.card>
</x-layout.app>
