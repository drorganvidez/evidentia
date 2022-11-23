<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <x-li route="admin.instance.create" icon='fas fa-box' name="Crear instancia"/>

        <x-li route="admin.instance.manage" secondaries="admin.instance.manage.edit,admin.instance.manage.delete" icon='fas fa-boxes' name="Gestionar instancias"/>

        <x-li route="admin.redesSociales.create" icon='fas fa-box' name="Añadir contraseña red social"/>

        <x-li route="admin.redesSociales.manage" icon='fas fa-boxes' name="Gestionar contraseñas redes sociales"/>

        <x-lilogout/>

    </ul>
</nav>
