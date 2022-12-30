@extends('layouts.app')

@section('title', 'Instancias')

@section('submenu')

    <x-submenus.instances-menu></x-submenus.instances-menu>

@endsection

@section('content')

    @if(Session::has('instance'))

        <div class="row">

            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="alert alert-info" role="alert">
                    Las credenciales de acceso como profesor para la instancia '{{ Session::get('instance')->name }}' son:
                    <br><br>
                    <ul>
                        <li>Usuario: {{env('LECTURE_NEW_INSTANCE_USERNAME')}}</li>
                        <li>Contraseña: {{env('LECTURE_NEW_INSTANCE_PASSWORD')}}</li>
                    </ul>
                    Aviso: las credenciales solo se mostrarán esta vez por seguridad. Guárdalas en lugar seguro.
                    Recuerda que puedes cambiarlas en cualquier momento.
                </div>
            </div>

        </div>

    @endif

    <x-data-table>

        {{-- Data --}}
        <x-slot:data>
            {{$instances}}
        </x-slot:data>

        {{-- Columns --}}
        <x-slot:columns>
            Nombre del curso | name;
            Ruta | route;
            Servidor MySQL | host;
            Puerto MySQL | port;
            Base de datos | database;
            Usuario MySQL | username;
            Password MYSQL | password;
            Fecha de creación | created_at | {"type" : "datetime"}
        </x-slot:columns>

        <x-slot:edit_item_route>
            admin.instances.edit
        </x-slot:edit_item_route>

        <x-slot:delete_item_route>
            developer.deleteapitoken_p
        </x-slot:delete_item_route>

        <x-slot:delete_item_message>
            Esto borrará la instancia y <b>todos los archivos asociados a ella (evidencias, reuniones, etc).</b>
        </x-slot:delete_item_message>

        <x-slot:mass_delete_route>
            developer.deletemassapitoken_p
        </x-slot:mass_delete_route>

        <x-slot:mass_delete_message>
            Todas las instancias serán borrada junto con <b>todos los archivos asociados a ellas (evidencias, reuniones, etc).</b>
        </x-slot:mass_delete_message>


        {{--
        <x-slot:disable_selection>
        </x-slot:disable_selection>
        --}}

        {{--
        <x-slot:disable_pagination>
        </x-slot:disable_pagination>
        --}}

        {{--
        <x-slot:disable_search>
        </x-slot:disable_search>
        --}}

        <x-slot:create_item_route>
            admin.instances.create
        </x-slot:create_item_route>

        <x-slot:create_item_message>
            No hemos encontrado ninguna instancia en el sistema
        </x-slot:create_item_message>

    </x-data-table>

@endsection
