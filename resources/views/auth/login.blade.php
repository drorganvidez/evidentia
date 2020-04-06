@extends('layouts.app')

@section('title', 'Acceso del alumno')

@section('title-icon', 'fas fa-door-open')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">
                <span class="badge badge-secondary">{{\Instantiation::instance_entity()->name}}</span>
            </h3>

        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-lg-8 col-sm-12">

                        <x-status/>

                        <form action="{{route('instance.login_p',$instance)}}" method="post">
                            @csrf

                            <x-input col="12" attr="username" type="email" label="Email de la US" description="Introduce tu email de la US"/>

                            <x-input col="12" attr="password" type="password" label="Contraseña" description="Introduce tu contraseña"/>

                            <div class="form-group col-md-8">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="remember">
                                    <label for="remember">
                                        Recuérdame
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-sm-12 col-lg-6">
                                <button type="submit" class="btn btn-primary btn-block">Acceder</button>
                            </div>

                            <div class="row">

                                <!-- /.col -->

                                <!-- /.col -->
                            </div>

                        </form>

                        <!-- <div class="card-body login-card-body">
                            <p class="login-box-msg">Accede a tu cuenta</p>

                            <form action="{{route('instance.login_p',$instance)}}" method="post">
                                @csrf
                                <div class="input-group mb-3">
                                    <input type="email" name="username" class="form-control" placeholder="Email de la US" autofocus>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-envelope"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Contraseña">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-lock"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-8">
                                        <div class="icheck-primary">
                                            <input type="checkbox" id="remember">
                                            <label for="remember">
                                                Recuérdame
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-primary btn-block">Acceder</button>
                                    </div>
                                </div>
                            </form>

                            <p class="mb-1">
                                <a href="forgot-password.html">Olvidé mi contraseña</a>
                            </p>
                        </div> -->


                </div>
                <div class="col-lg-4 col-sm-12">
                    <x-evidentiadescription/>
                </div>
            </div>
        </div>
    </div>


@endsection
