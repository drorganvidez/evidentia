@extends('layouts.app')

@section('title', 'Desarrollador')

@section('submenu')

    <x-submenus.developer-menu/>

@endsection

@section('content')

    <form method="post" action="{{route('developer.createapitoken_p',\Instantiation::instance())}}">

        @csrf

        <div class="row">
            <x-input>
                <x-slot:name>
                    token_name
                </x-slot:name>
                <x-slot:value>

                </x-slot:value>
                <x-slot:label>
                    Nombre del token
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:description>
                    Usa un nombre identificativo para el API token
                </x-slot:description>
                <x-slot:autofocus></x-slot:autofocus>
            </x-input>
        </div>

        <x-submit>
            <x-slot:name>
                Crear
            </x-slot:name>
        </x-submit>

    </form>

@endsection
