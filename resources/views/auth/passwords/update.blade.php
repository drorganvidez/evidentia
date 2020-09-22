@extends('layouts.app')

@section('title', 'Actualizar contraseña')

@section('title-icon', 'fas fa-unlock-alt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/{{\Instantiation::instance()}}">Acceso del alumno</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-6 col-sm-12">

            <x-status/>

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge badge-secondary">{{\Instantiation::instance_entity()->name}}</span>
                    </h3>

                </div>

                <div class="card-body">
                    <form action="{{route('password.update_p',["instance" => $instance, "token" => $token])}}" method="post">
                        @csrf

                        <div class="form-row">
                            <x-input col="12" attr="password" :required="false" type="password" :required="true" label="Nueva contraseña"/>
                        </div>

                        <div class="form-row">
                            <x-input col="12" attr="password_confirmation" :required="false" type="password" :required="true" label="Repite la nueva contraseña"/>
                        </div>

                        <div class="form-row">
                            <div class="col-lg-6 mt-1">
                                <button type="submit"  class="btn btn-primary btn-block">Actualizar contraseña</button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>

        <div class="col-lg-6 col-sm-12">

            <div class="callout callout-info">
                <x-evidentiadescription/>
            </div>



        </div>
    </div>

@endsection
