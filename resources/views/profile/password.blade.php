@extends('layouts.app')

@section('title', 'Perfil')

@section('submenu')

    <x-submenus.profile-menu></x-submenus.profile-menu>

@endsection

@section('content')

    <form method="post" action="{{route('profile.password_p',\Instantiation::instance())}}">

        @csrf

        <div class="row">
            <x-input>
                <x-slot:name>
                    current_password
                </x-slot:name>
                <x-slot:type>
                    password
                </x-slot:type>
                <x-slot:value>

                </x-slot:value>
                <x-slot:label>
                    Contraseña actual
                </x-slot:label>
                <x-slot:description>
                    Introduce la contraseña actual de Evidentia
                </x-slot:description>
                <x-slot:col>
                    col-12 col-md-4
                </x-slot:col>
                <x-slot:required></x-slot:required>
                <x-slot:autofocus>
                </x-slot:autofocus>
            </x-input>
        </div>

        <div class="row">
            <x-input>
                <x-slot:name>
                    password
                </x-slot:name>
                <x-slot:type>
                    password
                </x-slot:type>
                <x-slot:value>

                </x-slot:value>
                <x-slot:label>
                    Nueva contraseña
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-4
                </x-slot:col>
                <x-slot:description>
                    Al menos 8 caracteres, incluyendo mayúsculas, minúsculas, número y algún
                    carácter especial (! $ # % . @)
                </x-slot:description>
                <x-slot:required></x-slot:required>
            </x-input>
        </div>

        <div class="row">
            <x-input>
                <x-slot:name>
                    password_confirmation
                </x-slot:name>
                <x-slot:type>
                    password
                </x-slot:type>
                <x-slot:value>

                </x-slot:value>
                <x-slot:label>
                    Repite la nueva gcontraseña
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-4
                </x-slot:col>
                <x-slot:required></x-slot:required>
            </x-input>
        </div>

        <x-submit>
            <x-slot:name>
                Cambiar contraseña
            </x-slot:name>
        </x-submit>

    </form>

@endsection
