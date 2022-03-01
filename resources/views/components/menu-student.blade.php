@if(\Illuminate\Support\Facades\Auth::user()->hasRole('STUDENT'))

    <hr class="navbar-divider my-3">

    <h6 class="navbar-heading">
        Trabajo personal
    </h6>

    <ul class="navbar-nav">

    <x-item-menu>
        <x-slot:route>
            evidences
        </x-slot>
        <x-slot:icon>
        box
        </x-slot>
        <x-slot:name>
        Evidencias
        </x-slot>
        <x-slot:subitems>
        Nueva evidencia, evidences.create;
        En borrador, evidences.draft;
        Pendientes, evidences.pending;
        Aceptadas, evidences.accepted;
        Rechazadas, evidences.rejected
        </x-slot>
    </x-item-menu>

    </ul>

@endif

