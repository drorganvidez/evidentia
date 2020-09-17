@extends('layouts.app')

@section('title', 'Eliminar instancia')
@section('title-icon', 'fas fa-trash')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('admin.instance.manage')}}">Gestionar instancias</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">

                    <div class="alert alert-danger alert-dismissible">
                        <h5><i class="icon fas fa-ban"></i> ¡Acción irreversible!</h5>
                        Esta acción no se puede deshacer. Borrar una instancia implica borrar por completo la base de datos y
                        todos los archivos subidos por los usuarios.
                    </div>

                    <div class="callout callout-info">
                        <h5>Estás a punto de eliminar la siguiente instancia</h5>

                        <br>

                        <dl class="row">
                            <dt class="col-sm-4">Nombre del curso</dt>
                            <dd class="col-sm-8">{{$instance->name}}</dd>
                            <dt class="col-sm-4">Ruta</dt>
                            <dd class="col-sm-8">{{$instance->route}}</dd>
                            <dt class="col-sm-4">Puerto</dt>
                            <dd class="col-sm-8">{{$instance->port}}</dd>
                            <dt class="col-sm-4">Base de datos</dt>
                            <dd class="col-sm-8">{{$instance->database}}</dd>
                            <dt class="col-sm-4">Nombre de usuario</dt>
                            <dd class="col-sm-8">{{$instance->username}}</dd>
                            <dt class="col-sm-4">Contraseña</dt>
                            <dd class="col-sm-8">{{$instance->password}}</dd>
                        </dl>
                    </div>

                    <form method="POST" action="{{ route('admin.instance.manage.remove') }}">
                        @csrf

                        <div class="form-row">

                            <input type="hidden" name="id" value="{{$instance->id}}"/>

                            <x-input col="6" attr="name" label="Medida de seguridad" description="Introduzca el nombre del curso de la instancia"/>
                        </div>

                        <div class="form-row">
                            <div class="col-12 col-lg-3 col-sm-4">
                                <button type="submit" class="btn btn-danger btn-block">Eliminar instancia</button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
