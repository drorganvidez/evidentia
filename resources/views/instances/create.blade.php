@extends('layouts.app')

@section('title', 'Crear instancia')
@section('title-icon', 'fas fa-box')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.instance.new') }}">
                        @csrf

                        <div class="form-row">

                            <x-input col="4" attr="name" label="Nombre del curso" description="Ejemplo: curso 2019/2020"/>

                            <x-input col="4" attr="route" label="Ruta por defecto" description="Define una ruta de acceso para el curso"/>
                        </div>


                        <div class="form-row">

                            <x-input col="4" attr="host" label="Host" placeholder="localhost"/>

                            <x-input col="4" attr="port" label="Puerto" placeholder="3306"/>

                            <x-input col="4" attr="database" label="Base de datos" description="Nombre de la base de datos para la instancia"/>

                            <x-input col="4" attr="username" label="Nombre de usuario" description="Nombre de usuario que gestiona la base"/>

                            <x-input col="4" type="password" attr="password" label="Contraseña" description="Contraseña del usuario que gestiona la base"/>

                        </div>

                        <button type="submit" class="btn btn-primary">Crear instancia</button>

                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
