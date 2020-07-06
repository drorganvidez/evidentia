@if(\Illuminate\Support\Facades\Auth::user()->hasRole('LECTURE'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">HERRAMIENTAS DEL PROFESOR</li>
            <x-li route="lecture.config" icon='fas fa-cogs' name="Configurar curso"/>
            <x-li route="lecture.user.list" icon='nav-icon fas fa-users-cog' name="Gestionar alumnos"/>
            <x-li route="lecture.integrity" icon='fas fa-check-double' name="Comprobar integridad"/>
            <x-li route="lecture.import" icon='fas fa-file-import' name="Importaciones"/>
            <x-li route="lecture.export" icon='fas fa-file-export' name="Exportaciones"/>

        </ul>
    </nav>
@endif
