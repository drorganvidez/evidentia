<ul class="navbar-nav">

    <x-item-menu>
        <x-slot:route>
            home
        </x-slot:route>
        <x-slot:icon>
            home
        </x-slot:icon>
        <x-slot:name>
            Dashboard
        </x-slot:name>
    </x-item-menu>

    <x-item-menu>
        <x-slot:route>
            profile
        </x-slot:route>
        <x-slot:icon>
            user
        </x-slot:icon>
        <x-slot:name>
            Perfil
        </x-slot:name>
        <x-slot:subitems>
            Datos personales, profile.data;
            Avatar, profile.avatar;
            Cambiar contraseña, profile.password
        </x-slot:subitems>
    </x-item-menu>

    <x-item-menu>
        <x-slot:route>
            configuration
        </x-slot:route>
        <x-slot:icon>
            settings
        </x-slot:icon>
        <x-slot:name>
            Ajustes
        </x-slot:name>
        <x-slot:subitems>
            Notificaciones, settings.notifications
        </x-slot:subitems>
    </x-item-menu>

    <x-item-menu>
        <x-slot:route>
            developer
        </x-slot:route>
        <x-slot:icon>
            code
        </x-slot:icon>
        <x-slot:name>
            Desarrollador
        </x-slot:name>
        <x-slot:subitems>
            API docs, developer.apidocs;
            Crear API token, developer.createapitoken;
            Mis API tokens, developer.apitokens
        </x-slot:subitems>
    </x-item-menu>

</ul>