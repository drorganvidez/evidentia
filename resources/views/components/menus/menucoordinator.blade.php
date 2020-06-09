@if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">COMITÉ</li>
            <x-li route="home" icon='fas fa-globe-europe' name="Resumen"/>
            <x-li route="home" icon='fas fa-user-friends' name="Gestionar comité"/>

            <x-li route="coordinator.evidence.list.all"
                  secondaries="coordinator.evidence.view,coordinator.evidence.list.all,coordinator.evidence.list.pending,coordinator.evidence.list.accepted,coordinator.evidence.list.rejected"
                  icon='fas fa-clipboard-check' name="Gestionar evidencias"/>

        </ul>
    </nav>
@endif

