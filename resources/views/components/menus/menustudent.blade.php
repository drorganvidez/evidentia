@if(\Illuminate\Support\Facades\Auth::user()->hasRole('STUDENT'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">MISCELLANEOUS</li>
            <x-li route="evidence.create" icon='fab fa-angellist' name="Crear evidencia"/>

            <x-li route="evidence.list" icon='fab fa-battle-net' name="Mis evidencias"/>

            <x-lilogout/>

        </ul>
    </nav>
@endif

