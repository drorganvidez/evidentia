@extends('layouts.app')

@section('title', 'Desarrollador')

@section('submenu')

    <x-submenus.developer-menu/>

@endsection

@section('content')

    @if(Session::has('token'))

        <div class="row">

            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="alert alert-info" role="alert">
                    Tu API token generado es
                    <br>
                    <b>{{ Session::get('token') }}</b>
                    <br><br>
                    Aviso: tu API token solo se mostrará esta vez por seguridad. Guárdalo en lugar seguro. Recuerda que puedes revocar
                    el acceso en cualquier momento.
                </div>
            </div>

        </div>

    @endif



    <x-data-table>

        {{-- Data --}}
        <x-slot:data>
            {{$api_tokens}}
        </x-slot:data>

        {{-- Columns --}}
        <x-slot:columns>
            Nombre del token | name;
            Última vez usado | last_used_at | {"type" : "ago", "default" : "Nunca"};
            Creado el | created_at | {"type" : "datetime"}
        </x-slot:columns>

        {{-- Filters --}}
        <x-slot:filters>
            Nombre, name, valor1:valor2:valor3;
            Última vez usado, last_used_at, valor1:valor2:valor3:valor4
        </x-slot:filters>


        <x-slot:edit_item_route>
            developer.editapitoken
        </x-slot:edit_item_route>

        <x-slot:delete_item_route>
            developer.deleteapitoken_p
        </x-slot:delete_item_route>

        <x-slot:delete_item_message>
            Esto borrará el token. Los endpoints de la API de Evidentia que estén enlazados a dicho token dejarán de ser accesibles.
        </x-slot:delete_item_message>

        <x-slot:mass_delete_route>
            developer.deletemassapitoken_p
        </x-slot:mass_delete_route>

        <x-slot:mass_delete_message>
            Todos los tokens seleccionados serán borrados.
        </x-slot:mass_delete_message>


        <x-slot:actions>

        </x-slot:actions>


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

    </x-data-table>


@endsection
