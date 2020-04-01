@if(\Illuminate\Support\Facades\Auth::user()->hasRole('PRESIDENT'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">PRESIDENCIA</li>
            <x-li route="home" icon='fas fa-users-cog' name="Gestionar alumnos"/>
            <x-li route="home" icon='fas fa-user-friends' name="Gestionar reuniones"/>

        </ul>
    </nav>
@endif
