@extends('layouts.app')

@section('title', 'Acceso del alumno')

@section('title-icon', 'fas fa-door-open')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
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
                    <form action="{{route('instance.login_p',$instance)}}" method="post">
                        @csrf

                        <x-input col="12" attr="username" type="text" label="UVUS" description="Introduce tu UVUS (sin @alum.us.es)"/>

                        <x-input col="12" attr="password" type="password" label="Contraseña" description="Introduce tu contraseña"/>

                        <div class="form-group col-md-8 mb-0">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">
                                    &nbsp;Mantén la sesión iniciada
                                </label>
                            </div>
                        </div>

                        <div class="form-group col-sm-12 col-lg-12">
                            <a href="{{route('password.reset',$instance)}}">¿Olvidaste tu contraseña?</a>
                        </div>

                        <div class="form-group col-sm-12 col-lg-6">
                            <button type="submit" class="btn btn-primary btn-block">Acceder</button>

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
