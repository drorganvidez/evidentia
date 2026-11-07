{{--
    People of the course for the lecturer and the president: filters, a search on the server, a sortable table in
    pages, new user and mass actions
--}}
@use('App\Support\ListPage')
@php
    $isPresident = Auth::user()->hasRole('PRESIDENT');
    $prefix = $isPresident ? 'president' : 'lecture';
    $list = route($prefix . '.user.list');
    $roleNames = [
        'STUDENT' => 'Estudiante',
        'COORDINATOR' => 'Coordinador',
        'SECRETARY' => 'Secretario',
        'REGISTER_COORDINATOR' => 'Coordinador de registro',
        'PRESIDENT' => 'Presidente',
        'LECTURE' => 'Profesor',
    ];
    $active = count(array_filter($filters));
    $columns = $sort->columns([
        ['label' => 'Apellidos y nombre', 'key' => 'surname'],
        ['label' => 'UVUS', 'key' => 'username'],
        ['label' => 'DNI', 'key' => 'dni'],
        ['label' => 'Roles', 'key' => 'roles'],
        ['label' => 'Acciones', 'srOnly' => true],
    ]);
    $roleOptions = [
        ['value' => '', 'label' => 'Todos'],
        ...$roles->map(fn($role) => ['value' => $role->rol, 'label' => $roleNames[$role->rol] ?? $role->slug])->all(),
    ];
    $newUserFields = ['name' => 'Nombre', 'surname' => 'Apellidos', 'email' => 'Correo', 'username' => 'UVUS'];
@endphp
<x-layout.app title="Alumnos" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Alumnos']]">
    <x-ui.page-header title="Alumnos">
        <x-slot:actions>
            @if (Auth::user()->hasRole('LECTURE'))
                <x-ui.button icon="import" :href="route('lecture.import')">Importar</x-ui.button>
                <x-ui.dialog id="normalize-users" title="Arreglar los datos" trigger="Arreglar datos"
                    trigger-icon="wand-sparkles" description="Se aplica a todas las cuentas salvo las del profesorado.">
                    <form method="post" action="{{ route('management.user.normalize') }}" class="grid gap-3">
                        @csrf
                        <x-ui.choice name="fix_email" label="Poner el correo oficial"
                            hint="UVUS@alum.us.es, salvo si ya lo usa otra cuenta" />
                        <x-ui.choice name="fix_uppercase" label="Pasar nombres y apellidos a mayúsculas" />
                        <x-ui.choice name="fix_access" label="Dar acceso a todos"
                            hint="Desbloquea las cuentas bloqueadas" />
                        <div class="mt-2 flex justify-end gap-2">
                            <x-ui.button type="button" x-on:click="close">Cancelar</x-ui.button>
                            <x-ui.button variant="primary" type="submit" icon="wand-sparkles">Arreglar</x-ui.button>
                        </div>
                    </form>
                </x-ui.dialog>
                <x-ui.dialog id="delete-all-users" variant="alert" title="Borrar todos los usuarios"
                    description="Se borrarán todos los usuarios salvo el tuyo, con sus evidencias, mensajes, avisos, asistencias y las reuniones que registraron. Esta acción no se puede deshacer."
                    trigger="Borrar todos" trigger-icon="trash-2">
                    <x-slot:footer>
                        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                        <form method="post" action="{{ route('management.user.delete.all') }}">
                            @csrf
                            <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, borrar
                                todos</x-ui.button>
                        </form>
                    </x-slot:footer>
                </x-ui.dialog>
            @endif
            <x-ui.dialog id="new-user" title="Nuevo usuario" trigger="Nuevo usuario" trigger-variant="primary"
                trigger-icon="user-plus" :open-on-load="$errors->hasAny(array_keys($newUserFields))">
                <x-ui.form :action="route('management.user.new')" id="new-user-form" :fields="$newUserFields" busy-label="Añadiendo">
                    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
                        <x-ui.field name="name" label="Nombre" required autocomplete="off" />
                        <x-ui.field name="surname" label="Apellidos" required autocomplete="off" />
                        <x-ui.field name="email" label="Correo" type="email" required autocomplete="off" />
                        <x-ui.field name="username" label="UVUS" required autocomplete="off" />
                    </div>
                    <x-slot:actions>
                        <x-ui.button type="submit" variant="primary" icon="user-plus">Añadir usuario</x-ui.button>
                    </x-slot:actions>
                </x-ui.form>
            </x-ui.dialog>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :action="$list" :open="$active > 0" :active="$active" :reset-href="ListPage::url(['rol' => null, 'estado' => null], $list)" :keep="ListPage::query(['rol', 'estado'])">
        <x-ui.field name="rol" label="Rol" type="select" :value="$filters['rol']" :options="$roleOptions" />
        <x-ui.field name="estado" label="Estado" type="select" :value="$filters['estado']" :options="[
            ['value' => '', 'label' => 'Todos'],
            ['value' => 'activos', 'label' => 'Activos'],
            ['value' => 'bloqueados', 'label' => 'Bloqueados'],
        ]" />
    </x-ui.filter-bar>

    <x-ui.table caption="Usuarios" :columns="$columns" :paginator="$users" filterable count-label="usuarios"
        count-singular="usuario" filter-placeholder="Buscar usuarios">
        @if ($users->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="users" :title="$term !== null ? 'Ningún usuario coincide con la búsqueda' : 'No hay usuarios'" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($users as $user)
            @php
                $href = route('profiles.view', ['id' => $user->id]);
                $fullName = trim($user->surname . ', ' . $user->name, ', ');
            @endphp
            <x-ui.table.row :href="$href" :flag="$user->block ? 'Bloqueado' : null">
                <x-ui.table.cell label="Apellidos y nombre" primary>
                    <a href="{{ $href }}">{{ $fullName }}</a>
                    <span class="mt-0.5 block text-sm text-ink-muted">{{ $user->email }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="UVUS" meta><span
                        class="font-mono">{{ $user->username }}</span></x-ui.table.cell>
                <x-ui.table.cell label="DNI" meta hide><span
                        class="font-mono">{{ $user->dni }}</span></x-ui.table.cell>
                <x-ui.table.cell label="Roles" full>
                    <span class="flex flex-wrap items-center gap-1.5">
                        @if ($user->block)
                            <x-ui.badge tone="rejected" size="sm">Bloqueado</x-ui.badge>
                        @endif
                        @foreach ($user->roles as $role)
                            @php
                                $committee = match ($role->rol) {
                                    'COORDINATOR' => $user->coordinator?->committee?->name,
                                    'SECRETARY' => $user->secretary?->committee?->name,
                                    default => null,
                                };
                            @endphp
                            <x-ui.badge variant="chip"
                                size="sm">{{ $roleNames[$role->rol] ?? $role->slug }}{{ $committee ? ' de ' . $committee : '' }}</x-ui.badge>
                        @endforeach
                    </span>
                </x-ui.table.cell>
                <x-ui.table.cell end class="w-[1%] whitespace-nowrap">
                    <x-ui.button icon-only icon="user-cog" :label="'Gestionar ' . $fullName" :href="route($prefix . '.user.management', ['id' => $user->id])" variant="ghost" />
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
