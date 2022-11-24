@extends('layouts.app')

@section('title', 'Gestionar contraseñas')
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
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Red Social</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Contraseña</th>

                            <th>Opciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($redsocial as $r)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$r->name}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$r->password}}</td>

                                <td>
                                    <a class="btn btn-info btn-sm" href="{{route('admin.redesSociales.manage.edit',$r->id)}}" role="button">
                                        <i class="fas fa-edit"></i> <span class="d-none d-sm-none d-md-none d-lg-inline">Editar</span>
                                    </a>

                                    <a class="btn btn-danger btn-sm" href="{{route('admin.redesSociales.manage.delete',$r->id)}}" role="button">
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