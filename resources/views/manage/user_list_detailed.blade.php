@extends('layouts.app')

@section('title', 'Miembros del comité')

@section('title-icon', 'nav-icon fas fa-users-cog')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')


    <div class="row">
        <div class="col-lg-8">

            <div class="row mb-3">
                <p style="padding: 5px 25px 0px 15px">Exportar tabla:</p>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('president.manage.student.export',['instance' => $instance, 'ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('president.manage.student.export',['instance' => $instance, 'ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('president.manage.student.export',['instance' => $instance, 'ext' => 'pdf'])}}"
                       class="btn btn-info btn-block" role="button">
                        PDF</a>
                </div>
            </div>

            <div class="col-lg-9">

            <div class="card shadow-lg">

                <div class="card-body">

                    <div class="row" style="margin-bottom: 20px">
                        <div class="col-lg-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default">Acciones</button>
                                <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu" style="">

                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('LECTURE'))

                                        <a class="dropdown-item" href="{{route('lecture.import',\Instantiation::instance())}}">Importación masiva de usuarios</a>

                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#borrar_todo">
                                        Borrar todos los usuarios
                                    </a>

                                    @endif

                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('PRESIDENT'))
                                            <a class="dropdown-item" href="#">Coming soon...</a>
                                    @endif
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>

                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">DNI</th>
                            <th>Apellidos</th>
                            <th>Nombre</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">UVUS</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Roles</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas en evidencias</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas en reuniones</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas en eventos</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas en bonos</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas totales</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($users as $user)

                            @if($user->id != Auth::id())

                                @if($user->block)
                                    <tr class="bg-danger">
                                @else
                                    <tr>
                                @endif

                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->dni}}</td>
                                        <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->surname}}</a></td>
                                        <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->name}}</a></td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->username}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                            <x-roles :user="$user"/>
                                        </td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->evidences_accepted_hours()}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->meetings_hours()}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->events_hours()}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->bonus_hours()}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->total_computed_hours()}}</td>
                                    </tr>

                            @endif

                        @endforeach

                        </tbody>
                    </table>

                </div>
                
            </div>

        
    </div>

@endsection
