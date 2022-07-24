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

    {{route('developer.deleteapitoken_p',\Instantiation::instance())}}

    <x-data-table>

        <x-slot:data>
            {{$api_tokens}}
        </x-slot:data>

        <x-slot:columns>
            Nombre del token, name;
            Última vez usado, last_used_at
        </x-slot:columns>

        <x-slot:filters>
            Nombre, name, valor1:valor2:valor3;
            Última vez usado, last_used_at, valor1:valor2:valor3:valor4
        </x-slot:filters>

        <x-slot:delete-item-message>

        </x-slot:delete-item-message>

    </x-data-table>


@endsection
