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

            <div class="card shadow-lg">

                <div class="card-body">
                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th>Curso</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Ruta</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Host</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Puerto</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">BBDD</th>
                            <th class="d-none d-sm-none d-md-none d-lg-table-cell">Usuario</th>
                            <th class="d-none d-sm-none d-md-none d-lg-table-cell">Pass</th>
                            <th>Opciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($instances as $instance)
                            <tr>
                                <td>{{$instance->name}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$instance->route}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$instance->host}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$instance->port}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$instance->database}}</td>
                                <td class="d-none d-sm-none d-md-none d-lg-table-cell">{{$instance->username}}</td>
                                <td class="d-none d-sm-none d-md-none d-lg-table-cell">{{$instance->password}}</td>

                                <td>
                                    <a class="btn btn-primary btn-sm" href="/{{$instance->route}}" role="button">
                                        <i class="fas fa-sign-in-alt"></i> <span class="d-none d-sm-none d-md-none d-lg-inline">Acceder</span>
                                    </a>

                                    <a class="btn btn-info btn-sm" href="{{route('admin.instance.manage.edit',$instance->id)}}" role="button">
                                        <i class="fas fa-edit"></i> <span class="d-none d-sm-none d-md-none d-lg-inline">Editar</span>
                                    </a>

                                    <a class="btn btn-danger btn-sm" href="{{route('admin.instance.manage.delete',$instance->id)}}" role="button">
                                        <i class="fas fa-trash"></i> <span class="d-none d-sm-none d-md-none d-lg-inline">Eliminar</span>
                                    </a>

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
