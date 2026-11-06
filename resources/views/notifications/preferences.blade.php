{{--
    Which notices also arrive by mail. The fixed ones always do (their switches are disabled and never sent); a switch
    left off sends nothing, so a missing mail[] means the mail is off. The daily digest is only for coordinators and
    secretaries.
--}}
@php
    $switchId = fn(string $key): string => 'mail-' . str_replace('.', '-', $key);
@endphp
<x-layout.app title="Preferencias de avisos" nav-current="profile.view" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Mi perfil', 'href' => route('profile.view')],
    ['label' => 'Preferencias de avisos'],
]" :notifications="['count' => $unread, 'href' => route('notifications.index')]">
    <x-ui.page-header title="Preferencias de avisos">
        <x-slot:meta>
            <x-ui.badge variant="chip">{{ $me->email }}</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <x-ui.button variant="ghost" icon="bell" :href="route('notifications.index')">Avisos</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.form :action="route('notifications.preferences.save')" :fields="['mail' => 'Avisos por correo', 'digest' => 'Resumen diario']" busy-label="Guardando" id="preferences-form">
        @if ($catalog['fixed'] !== [])
            <x-ui.card title="Siempre en la bandeja y por correo" :level="2" id="fixed-title">
                <ul class="grid gap-2">
                    @foreach ($catalog['fixed'] as $class)
                        <li>
                            <x-ui.choice type="switch" :name="'fixed_' . str_replace('.', '_', $class::key())" :label="$class::label()" :checked="true" disabled
                                hint="No se puede desactivar" :id="'fixed-' . str_replace('.', '-', $class::key())" />
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        @endif

        @if ($catalog['important'] !== [])
            <x-ui.card title="También por correo" :level="2" id="important-title">
                <ul class="grid gap-2">
                    @foreach ($catalog['important'] as $class)
                        <li>
                            <x-ui.choice type="switch" name="mail[]" :value="$class::key()" :label="$class::label()"
                                :checked="$prefs->mailEnabled($me, $class::key())" :id="$switchId($class::key())" />
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        @endif

        @if ($catalog['informative'] !== [])
            <x-ui.card title="Solo en la bandeja, salvo que lo pidas" :level="2" id="informative-title">
                <ul class="grid gap-2">
                    @foreach ($catalog['informative'] as $class)
                        <li>
                            <x-ui.choice type="switch" name="mail[]" :value="$class::key()" :label="$class::label()"
                                :checked="$prefs->mailEnabled($me, $class::key())" :id="$switchId($class::key())" />
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        @endif

        @if ($me->hasRole('COORDINATOR') || $me->hasRole('SECRETARY'))
            <x-ui.card title="Resumen diario" :level="2" id="digest-title">
                <x-ui.choice type="switch" name="digest" value="1"
                    label="Un correo al día con las evidencias recibidas en lugar de uno por evidencia"
                    :checked="$prefs->digest($me)" />
            </x-ui.card>
        @endif

        <x-slot:actions>
            <x-ui.button type="submit" variant="primary" icon="save">Guardar</x-ui.button>
        </x-slot:actions>
    </x-ui.form>
</x-layout.app>
