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
                                                    <span class="d-none d-sm-none d-md-none d-lg-inline">Gestionar</span>
                                                </a>
                                            @else
                                                <a class="btn btn-primary btn-sm" href="{{route('lecture.user.management',['instance' => $instance, 'id' => $user->id])}}">
                                                    <i class="nav-icon nav-icon fas fa-users-cog"></i>
                                                    <span class="d-none d-sm-none d-md-none d-lg-inline">Gestionar</span>
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
    </div>

@endsection
