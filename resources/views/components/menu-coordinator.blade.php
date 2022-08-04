@if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR'))

    <hr class="navbar-divider my-3">

    <h6 class="navbar-heading">
        Gestionar comité
    </h6>

    <ul class="navbar-nav">

        <x-item-menu>
            <x-slot:route>
                task
            </x-slot:route>
            <x-slot:icon>
                check-square
            </x-slot:icon>
            <x-slot:name>
                Moderar evidencias
            </x-slot:name>
            <x-slot:subitems>
                Nueva evidencia, evidences.create;
                En borrador, evidences.draft;
                Pendientes, evidences.pending;
                Aceptadas, evidences.accepted;
                Rechazadas, evidences.rejected
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
                Nueva evidencia, evidences.create;
                En borrador, evidences.draft;
                Pendientes, evidences.pending;
                Aceptadas, evidences.accepted;
                Rechazadas, evidences.rejected
            </x-slot:subitems>
        </x-item-menu>

    </ul>

@endif

