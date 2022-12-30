@extends('layouts.app')

@isset($item)
    @section('subtitle', 'Instancias')
    @section('title', 'Editando: ' . $item->name)
@else
    @section('title', 'Instancias')
@endisset

@if(!isset($item))
    @section('submenu')

        <x-submenus.instances-menu></x-submenus.instances-menu>

    @endsection
@endif

@section('content')

    @if(isset($item))

        <div class="row">

            <div class="col-lg-12">
                <a href="{{route('instances.list', \Instantiation::instance())}}" class="btn btn-outline-primary mb-4">
                    <i class="fe fe-skip-back"></i> Volver a mis instancias
                </a>
            </div>

        </div>

    @endisset

    <form method="post" action="{{route("$action",\Instantiation::instance())}}">

        @csrf

        @isset($item)
            <input type="hidden" name="_id" value="{{$item->id}}">
        @endisset

        <div class="row">

            <x-input>
                <x-slot:name>
                    name
                </x-slot:name>
                <x-slot:value>
                    {{$item->name ?? ''}}
                </x-slot:value>
                <x-slot:placeholder>
                    Curso 2022/23
                </x-slot:placeholder>
                <x-slot:label>
                    Nombre del curso
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:description>
                    Usa un nombre identificativo para el curso
                </x-slot:description>
                <x-slot:autofocus></x-slot:autofocus>
                <x-slot:required></x-slot:required>
            </x-input>

            <x-input>
                <x-slot:name>
                    route
                </x-slot:name>
                <x-slot:value>
                    {{$item->route ?? ''}}
                </x-slot:value>
                <x-slot:placeholder>
                    22
                </x-slot:placeholder>
                <x-slot:label>
                    Nombre de la ruta
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:description>
                    Usa una ruta como entrypoint
                </x-slot:description>
                <x-slot:required></x-slot:required>
            </x-input>
        </div>

        <div class="row">

            <x-input>
                <x-slot:name>
                    host
                </x-slot:name>
                <x-slot:value>
                    {{$item->host ?? env('DB_HOST') }}
                </x-slot:value>
                <x-slot:placeholder>
                    localhost
                </x-slot:placeholder>
                <x-slot:label>
                    Nombre del host
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:description>
                    Nombre del host de la base de datos
                </x-slot:description>
                <x-slot:required></x-slot:required>
            </x-input>

            <x-input>
                <x-slot:name>
                    port
                </x-slot:name>
                <x-slot:value>
                    {{$item->port ?? env('DB_PORT') }}
                </x-slot:value>
                <x-slot:placeholder>
                    3306
                </x-slot:placeholder>
                <x-slot:label>
                    Número del puerto
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:description>
                    Número del puerto de conexión con la base de datos
                </x-slot:description>
                <x-slot:required></x-slot:required>
            </x-input>

        </div>

        <div class="row">

            <x-input>
                <x-slot:name>
                    database
                </x-slot:name>
                <x-slot:value>
                    {{$item->port ?? '' }}
                </x-slot:value>
                <x-slot:placeholder>
                    base22
                </x-slot:placeholder>
                <x-slot:label>
                    Nombre de la base
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:description>
                    Nombre de la base de datos que se usará
                </x-slot:description>
                <x-slot:required></x-slot:required>
            </x-input>

            <x-input>
                <x-slot:name>
                    username
                </x-slot:name>
                <x-slot:value>
                    {{$item->username ?? env('DB_USERNAME') }}
                </x-slot:value>
                <x-slot:placeholder>
                    base22
                </x-slot:placeholder>
                <x-slot:label>
                    Nombre de usuario
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-3
                </x-slot:col>
                <x-slot:description>
                    Nombre del usuario de la base de datos
                </x-slot:description>
                <x-slot:required></x-slot:required>
            </x-input>

            <x-input>
                <x-slot:name>
                    password
                </x-slot:name>
                <x-slot:value>
                    {{$item->password ?? env('DB_PASSWORD') }}
                </x-slot:value>
                <x-slot:placeholder>
                    base22
                </x-slot:placeholder>
                <x-slot:label>
                    Contraseña
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-3
                </x-slot:col>
                <x-slot:description>
                    Contraseña del usuario de la base de datos
                </x-slot:description>
                <x-slot:required></x-slot:required>
            </x-input>

        </div>

        <livewire:instance-connection></livewire:instance-connection>

        <x-submit>
            <x-slot:name>
                Guardar
            </x-slot:name>
            <x-slot:id>
                save_instance
            </x-slot:id>
            <x-slot:disabled></x-slot:disabled>
        </x-submit>

    </form>

@endsection
