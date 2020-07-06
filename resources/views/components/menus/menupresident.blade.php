@if(\Illuminate\Support\Facades\Auth::user()->hasRole('PRESIDENT'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">PRESIDENCIA</li>

            <x-li route="president.user.list" icon='nav-icon fas fa-users-cog' name="Gestionar alumnos"/>
            <x-li route="president.evidence.list" icon='fas fa-clipboard-check' name="Gestionar evidencias"/>
            <x-li route="president.meeting.list" icon='far fa-handshake' name="Gestionar reuniones"/>

        </ul>
    </nav>
@endif
