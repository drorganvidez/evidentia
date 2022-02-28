<ul class="navbar-nav">

    <x-item-menu>
        <x-slot:route>
            home
        </x-slot>
        <x-slot:icon>
            home
        </x-slot>
        <x-slot:name>
            Dashboard
        </x-slot>
    </x-item-menu>

    <x-item-menu>
        <x-slot:route>
            profile
        </x-slot>
        <x-slot:icon>
            user
        </x-slot>
        <x-slot:name>
            Perfil
        </x-slot>
        <x-slot:subitems>
            Datos personales, profile.data;
            Avatar, profile.avatar;
            Cambiar contraseña, profile.password
        </x-slot>
    </x-item-menu>

    <x-item-menu>
        <x-slot:route>
            configuration
        </x-slot>
        <x-slot:icon>
            settings
        </x-slot>
        <x-slot:name>
            Ajustes
        </x-slot>
    </x-item-menu>

</ul>