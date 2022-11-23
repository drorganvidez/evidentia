@if(\Illuminate\Support\Facades\Auth::user()->hasRole('PRESIDENT'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">PRESIDENCIA</li>

            <x-li route="president.config" icon='fas fa-cogs' name="Configurar curso"/>
            <x-li route="president.user.list" secondaries="president.user.management" icon='nav-icon fas fa-users-cog' name="Gestionar alumnos"/>
            <x-li route="president.evidence.list" icon='fas fa-clipboard-check' name="Gestionar evidencias"/>
            <x-li route="president.meeting.list" icon='far fa-handshake' name="Gestionar reuniones"/>
            <x-li route="president.comittee.list" icon='fas fa-sitemap' name="Gestionar comités"/>
            <x-li route="president.export" icon='fas fa-file-export' name="Exportaciones"/>                 
            <x-li route="president.transaction.list" icon='fas fa-clipboard-check' name="Gestionar transacciones"/>
            

        </ul>
    </nav>
@endif
