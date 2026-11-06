{{-- Management of one person: personal data, access, roles with their committee, a new password and their hours --}}
@php
    $prefix = Auth::user()->hasRole('PRESIDENT') ? 'president' : 'lecture';
    $fullName = trim($user->surname . ', ' . $user->name, ', ');
    $roleNames = [
        'STUDENT' => 'Estudiante',
        'COORDINATOR' => 'Coordinador',
        'SECRETARY' => 'Secretario',
        'REGISTER_COORDINATOR' => 'Coordinador de registro',
        'PRESIDENT' => 'Presidente',
        'LECTURE' => 'Profesor',
    ];
    $roleOptions = $roles
        ->map(fn($role) => ['value' => (string) $role->id, 'label' => $roleNames[$role->rol] ?? $role->slug])
        ->values()
        ->all();
    $withCommittee = $roles
        ->whereIn('rol', ['COORDINATOR', 'SECRETARY'])
        ->pluck('id')
        ->map(fn($id) => "'" . $id . "'")
        ->implode(', ');
    $selectedRoles = old('roles', $user->roles->pluck('id')->map(fn($id) => (string) $id)->all());
    $needsCommittee = $roles
        ->whereIn('rol', ['COORDINATOR', 'SECRETARY'])
        ->pluck('id')
        ->map(fn($id) => (string) $id)
        ->intersect((array) $selectedRoles)
        ->isNotEmpty();
    $committeeId = $user->hasRole('COORDINATOR')
        ? $user->coordinator?->committee_id
        : ($user->hasRole('SECRETARY')
            ? $user->secretary?->committee_id
            : null);
    $access = session()->hasOldInput() ? old('pass') === 'on' : !$user->block;
    $hours = [
        ['En evidencias', $user->evidences_accepted_hours()],
        ['En reuniones', $user->meetings_hours()],
        ['En eventos', $user->events_hours()],
        ['Bonificadas', $user->bonus_hours()],
    ];
@endphp
<x-layout.app :title="'Gestionar a ' . $fullName" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Alumnos', 'href' => route($prefix . '.user.list')],
    ['label' => $fullName],
]">
    <x-ui.page-header :title="$fullName">
        <x-slot:eyebrow>
            <span>Gestionar usuario</span>
            @if ($user->block)
                <x-ui.badge tone="rejected" size="sm">Bloqueado</x-ui.badge>
            @endif
        </x-slot:eyebrow>
        <x-slot:actions>
            <x-ui.button icon="eye" :href="route('profiles.view', ['id' => $user->id])">Ver perfil</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <x-ui.form :action="$route" id="user-form" :fields="[
            'name' => 'Nombre',
            'surname' => 'Apellidos',
            'username' => 'UVUS',
            'email' => 'Correo',
            'roles' => 'Roles',
            'committee' => 'Comité',
            'password' => 'Nueva contraseña',
        ]">
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <x-ui.card title="Datos personales" :level="2">
                <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
                    <x-ui.field name="name" label="Nombre" :value="$user->name" required autocomplete="off" />
                    <x-ui.field name="surname" label="Apellidos" :value="$user->surname" required autocomplete="off" />
                    <x-ui.field name="username" label="UVUS" :value="$user->username" required autocomplete="off" />
                    <x-ui.field name="email" label="Correo" type="email" :value="$user->email" required
                        autocomplete="off" />
                </div>
            </x-ui.card>

            <x-ui.card title="Acceso y roles" :level="2">
                <div class="grid grid-cols-[minmax(0,1fr)] gap-6">
                    <x-ui.choice type="switch" name="pass" value="on" label="Permitir acceso a la aplicación"
                        :checked="$access" />
                    <x-ui.checkbox-group name="roles" legend="Roles" :options="$roleOptions" :selected="$selectedRoles"
                        :columns="2">
                        <x-slot:dependent>
                            <div x-show="hasAny({{ $withCommittee }})"
                                @unless ($needsCommittee) x-cloak @endunless class="glass-2 rounded-lg p-4">
                                <x-ui.field name="committee" label="Comité" type="select" :value="$committeeId"
                                    placeholder="Elige un comité" :options="$committees->pluck('name', 'id')->all()" />
                            </div>
                        </x-slot:dependent>
                    </x-ui.checkbox-group>
                </div>
            </x-ui.card>

            <x-ui.card title="Cambio de contraseña" :level="2">
                <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
                    <x-ui.field name="password" label="Nueva contraseña" type="password" autocomplete="new-password" />
                    <x-ui.field name="password_confirmation" label="Repite la nueva contraseña" type="password"
                        autocomplete="new-password" />
                </div>
            </x-ui.card>

            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="save">Guardar cambios</x-ui.button>
                <x-ui.button variant="ghost" :href="route($prefix . '.user.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>

        <aside aria-label="Horas" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Horas computadas" :level="2">
                <p><x-ui.hours :value="$user->total_computed_hours()" size="xl" class="font-bold tracking-tighter" /></p>
                <x-ui.dl class="mt-4">
                    @foreach ($hours as [$term, $value])
                        <x-ui.dl.item :term="$term"><x-ui.hours :value="$value" /></x-ui.dl.item>
                    @endforeach
                </x-ui.dl>
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
