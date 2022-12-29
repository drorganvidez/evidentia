@if(Request::is('admin') || Request::is('admin/*'))

    <hr class="navbar-divider my-3">

    <h6 class="navbar-heading">
        Opciones de administrador
    </h6>

    <ul class="navbar-nav">

        <x-item-menu>
            <x-slot:route>
                admin
            </x-slot:route>
            <x-slot:icon>
                database
            </x-slot:icon>
            <x-slot:name>
                Instancias
            </x-slot:name>
            <x-slot:subitems>
                Ver instancias, admin.instances.list;
                Crear instancia, admin.instances.create
            </x-slot:subitems>
        </x-item-menu>

    </ul>

@endif

