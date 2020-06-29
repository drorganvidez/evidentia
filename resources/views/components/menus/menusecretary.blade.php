@if(\Illuminate\Support\Facades\Auth::user()->hasRole('SECRETARY'))

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-header">REUNIONES</li>
            <x-li route="home" icon='far fa-handshake' name="Crear reuniones"/>
            <x-li route="home" icon='fas fa-pen-square' name="Gestionar reuniones"/>
            <x-li route="secretary.defaultlist.list" secondaries="secretary.defaultlist.create,secretary.defaultlist.edit" icon='far fa-list-alt' name="Gestionar listas"/>

        </ul>
    </nav>
@endif
