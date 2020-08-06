@extends('layouts.app')

@section('title', 'Gestionar alumnos')

@section('title-icon', 'nav-icon fas fa-users-cog')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>DNI</th>
                            <th>Apellidos</th>
                            <th>Nombre</th>
                            <th>UVUS</th>
                            <th>Roles</th>
                            <th>Opciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($users as $user)

                        @if($user->block)
                            <tr class="bg-danger">
                        @else
                            <tr>
                        @endif

                                <td>{{$user->dni}}</td>
                                <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->surname}}</a></td>
                                <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->name}}</a></td>
                                <td>{{$user->username}}</td>
                                <td>
                                    <x-roles :user="$user"/>
                                </td>
                                <td>
                                    @if(Auth::user()->hasRole('PRESIDENT'))
                                        <a class="btn btn-primary btn-sm" href="{{route('president.user.management',['instance' => $instance, 'id' => $user->id])}}">
                                            <i class="nav-icon nav-icon fas fa-users-cog"></i>
                                            Gestionar
                                        </a>
                                    @else
                                        <a class="btn btn-primary btn-sm" href="{{route('lecture.user.management',['instance' => $instance, 'id' => $user->id])}}">
                                            <i class="nav-icon nav-icon fas fa-users-cog"></i>
                                            Gestionar
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

@endsection
