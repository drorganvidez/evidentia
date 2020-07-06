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
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($users as $user)
                            <tr>
                                <td>{{$user->dni}}</td>
                                <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->surname}}</a></td>
                                <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->name}}</a></td>
                                <td>{{$user->username}}</td>
                            </tr>
                        @empty
                            <tr>
                                <td>Sin alumnos
                            </tr>
                        @endforelse

                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

@endsection
