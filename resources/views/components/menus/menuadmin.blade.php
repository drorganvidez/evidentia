<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <x-li route="admin.instance.create" icon='fas fa-box' name="Crear instancia"/>

        <x-li route="admin.instance.manage" secondaries="admin.instance.manage.edit,admin.instance.manage.delete" icon='fas fa-boxes' name="Gestionar instancias"/>

        <x-li route="admin.redesSociales.create" icon='fa fa-comment' name="Añadir contraseña red social"/>

        <x-li route="admin.redesSociales.manage" secondaries="admin.redesSociales.manage.edit,admin.redesSociales.manage.delete" icon='fa fa-comments' name="Gestionar contraseñas redes sociales"/>

        <x-li route="admin.empresasColaborativas.create" icon='fa fa-comment' name="Añadir empresa colaborativa"/>

        <x-li route="admin.empresasColaborativas.manage" secondaries="admin.empresasColaborativas.manage.edit,admin.empresasColaborativas.manage.delete" icon='fa fa-comments' name="Gestionar empresas colaborativas"/>

        <x-lilogout/>

    </ul>
</nav>
