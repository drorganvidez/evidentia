@if (\Illuminate\Support\Facades\Auth::user()->hasRole('REGISTER_COORDINATOR'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">ASISTENCIAS Y EVENTOS</li>
            @if (!\Carbon\Carbon::now()->gt(\Config::attendee_timestamp()))
                <x-li route="registercoordinator.token" icon='fas fa-screwdriver' name="Ajustes de Eventbrite" />
            @endif
            <x-li route="registercoordinator.event.list" icon='fas fa-calendar-alt' name="Gestionar eventos" />
            <x-li route="registercoordinator.attendee.list" icon='fas fa-user-check' name="Gestionar asistencias" />

        </ul>
    </nav>
@endif
