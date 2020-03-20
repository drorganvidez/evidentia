@extends('layouts.app')

@section('title', 'Gestionar instancias')
@section('title-icon', 'fas fa-boxes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <x-status/>

            <div class="card">

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Ruta</th>
                            <th>Host</th>
                            <th>Puerto</th>
                            <th>Base de datos</th>
                            <th>Nombre de usuario</th>
                            <th>Contraseña</th>
                            <th>Opciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($instances as $instance)
                            <tr>
                                <td>{{$instance->name}}</td>
                                <td>{{$instance->route}}</td>
                                <td>{{$instance->host}}</td>
                                <td>{{$instance->port}}</td>
                                <td>{{$instance->database}}</td>
                                <td>{{$instance->username}}</td>
                                <td>{{$instance->password}}</td>

                                <td>
                                    <a class="btn btn-primary btn-sm" href="/{{$instance->route}}" role="button">Acceder</a>
                                    <a class="btn btn-primary btn-sm" href="{{route('admin.instance.manage.edit',$instance->id)}}" role="button">Editar</a>
                                    <a class="btn btn-primary btn-sm" href="/{{$instance->route}}" role="button">Exportar</a>
                                    <a class="btn btn-danger btn-sm" href="{{route('admin.instance.manage.delete',$instance->id)}}" role="button">Eliminar</a>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>

@endsection
