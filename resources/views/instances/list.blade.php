@extends('layouts.app')

@section('title', 'Bienvenid@ a Evidentia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Acceso</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($instances as $instance)
                            <tr>
                                <td>{{$instance->name}}</td>
                                <td><a class="btn btn-primary" href="/{{$instance->route}}" role="button">Acceder</a></td>
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
