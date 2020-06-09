@if(\Illuminate\Support\Facades\Auth::user()->hasRole('STUDENT'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">HERRAMIENTAS</li>
            <x-li route="evidence.create" icon='fab fa-angellist' name="Crear evidencia"/>
            <x-li route="evidence.list" secondaries="evidence.view,evidence.edit" icon='fab fa-battle-net' name="Mis evidencias"/>
            <x-li route="home" icon='fas fa-cocktail' name="Mis reuniones"/>
            <!-- <x-li route="home" icon='fas fa-folder' name="Gestor de archivos"/> -->

        </ul>
    </nav>
@endif

