@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar instancia: '.$instance->name)
@else
    @section('title', 'Crear instancia')
@endisset

@section('title-icon', 'fas fa-box')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('admin.instance.manage')}}">Gestionar instancias</a></li>
    @endisset
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <form method="POST" action="{{$route}}">
                        @csrf

                        <x-id :id="$instance->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="name" :value="$instance->name ?? ''" label="Nombre del curso" description="Ejemplo: curso 2019/2020"/>

                        </div>

                        <div class="form-row">

                            <x-input col="4" attr="route" :value="$instance->route ?? ''" label="Ruta por defecto" description="Define una ruta de acceso para el curso"/>

                            <x-input col="4" attr="host" :value="$instance->host ?? 'localhost'" label="Host" placeholder="localhost"/>

                            <x-input col="4" attr="port" :value="$instance->port ?? '33060'" label="Puerto" placeholder="3306"/>

                            <x-input col="4" attr="database" disabled="true" :edit="$edit ?? ''" :value="$instance->database ?? ''" label="Base de datos" description="Nombre de la base de datos para la instancia"/>

                            <x-input col="4" attr="username" :value="$instance->username ?? 'homestead'" label="Nombre de usuario" description="Nombre de usuario que gestiona la base"/>

                            <x-input col="4" attr="password" :value="$instance->password ?? 'secret'" label="Contraseña" description="Contraseña del usuario que gestiona la base"/>

                        </div>

                        <div class="form-row">
                            <div class="col-12 col-lg-3 col-sm-4">
                                <button type="submit" class="btn btn-primary btn-block">Guardar instancia</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
