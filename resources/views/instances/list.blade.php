@extends('layouts.app')

@section('title', 'Instancias')

@section('submenu')

    <x-submenus.instances-menu></x-submenus.instances-menu>

@endsection

@section('content')

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
