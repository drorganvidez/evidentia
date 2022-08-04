@if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR'))

    <hr class="navbar-divider my-3">

    <h6 class="navbar-heading">
        Gestionar comité
    </h6>

    <ul class="navbar-nav">

        <x-item-menu>
            <x-slot:route>
                coordinator_evidences_moderate
            </x-slot:route>
            <x-slot:icon>
                check-circle
            </x-slot:icon>
            <x-slot:name>
                Evaluación
            </x-slot:name>
            <x-slot:subitems>
                Evidencias, coordinator.evidences.list;
                Modo moderación, coordinator.evidences.moderate
            </x-slot:subitems>
        </x-item-menu>

        <x-item-menu>
            <x-slot:route>
                task
            </x-slot:route>
            <x-slot:icon>
                trello
            </x-slot:icon>
            <x-slot:name>
                Tareas
            </x-slot:name>
            <x-slot:subitems>
                Tablero, coordinator.tasks.kanban;
                Informes, coordinator.tasks.reports
            </x-slot:subitems>
        </x-item-menu>

    </ul>

@endif

