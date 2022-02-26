@if(\Illuminate\Support\Facades\Auth::user()->hasRole('LECTURE'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">HERRAMIENTAS DEL PROFESOR</li>
            <x-li route="lecture.config" icon='fas fa-cogs' name="Configurar curso"/>

            <x-li route="lecture.user.list" secondaries="lecture.user.management,lecture.user.management" icon='nav-icon fas fa-users-cog' name="Gestionar alumnos"/>
            <x-li route="lecture.evidence.list" icon='fas fa-clipboard-check' name="Gestionar evidencias"/>
            <x-li route="lecture.meeting.list" icon='far fa-handshake' name="Gestionar reuniones"/>
            <x-li route="lecture.comittee.list" icon='fas fa-sitemap' name="Gestionar comités"/>

            <x-li route="randomize.randomize" icon='fas fa-random' name="Aleatorizar evidencias"/>

            <x-li route="lecture.integrity" icon='fas fa-check-double' name="Comprobar integridad"/>
            <x-li route="lecture.import" icon='fas fa-file-import' name="Importaciones"/>
            <x-li route="lecture.export" icon='fas fa-file-export' name="Exportaciones"/>
            <x-li route="lecture.instances.list" icon="nav-icon fas fa-boxes" name="Instancias" />

        </ul>
    </nav>
@endif
