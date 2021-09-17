@extends('layouts.app')

@section('title', 'Gestionar alumnos')

@section('title-icon', 'nav-icon fas fa-users-cog')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')


    <div class="row">
        <div class="col-lg-8">

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

                                    <a class="dropdown-item" href="#">Avisar por email de nuevas cuentas</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Borrar todos los usuarios</a>
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>
                    <table id="dataset" class="table table-hover">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">DNI</th>
                            <th>Apellidos</th>
                            <th>Nombre</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">UVUS</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Roles</th>
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
                                        <td>
                                            @if(Auth::user()->hasRole('PRESIDENT'))
                                                <a class="btn btn-primary btn-sm" href="{{route('president.user.management',['instance' => $instance, 'id' => $user->id])}}">
                                                    <i class="nav-icon nav-icon fas fa-users-cog"></i>
                                                </a>
                                            @else
                                                <a class="btn btn-primary btn-sm" href="{{route('lecture.user.management',['instance' => $instance, 'id' => $user->id])}}">
                                                    <i class="nav-icon nav-icon fas fa-users-cog"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>

                            @endif

                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Añadir nuevo usuario</h4>

                    <form method="POST" enctype="multipart/form-data" action="">
                        @csrf


                        <div class="form-row">
                            <x-input col="6" attr="name" label="Nombre"/>
                            <x-input col="6" attr="surname" label="Apellidos"/>
                        </div>

                        <div class="form-row">
                            <x-input col="6" attr="email" label="Email"/>
                            <x-input col="6" attr="dni"  label="DNI"/>
                        </div>

                        <div class="form-row">
                            <x-input col="6" attr="username"  label="UVUS"/>
                        </div>
                        <div class="form-row">

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary btn-block" data-dismiss="modal">
                                    <i class="fas fa-user-plus"></i>
                                    Añadir usuario</button>
                            </div>

                        </div>



                    </form>

                </div>

            </div>
        </div>
    </div>

@endsection
