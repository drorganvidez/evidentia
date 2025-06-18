@extends('layouts.app')

@section('title', 'Gestionar: ' . $user->surname . ', ' . $user->name)

@section('title-icon', 'nav-icon fas fa-cogs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    @if (Auth::user()->hasRole('PRESIDENT'))
        <li class="breadcrumb-item"><a href="{{ route('president.user.list') }}">Gestionar alumnos</a></li>
    @else
        <li class="breadcrumb-item"><a href="{{ route('lecture.user.list') }}">Gestionar alumnos</a></li>
    @endif
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <form method="POST" action="{{ $route }}">
        @csrf

        <input type="hidden" name="user_id" value="{{ $user->id }}">

        <div class="row">


            <div class="col-lg-7">

                <div class="card">

                    <div class="card-body">



                        <h3>Datos personales</h3>

                        <div class="form-row">

                            <x-input col="6" attr="username" :value="$user->username" label="Uvus" />

                        </div>

                        <div class="form-row">

                            <x-input col="6" attr="name" :value="$user->name" label="Nombre" />

                            <x-input col="6" attr="surname" :value="$user->surname" label="Apellidos" />

                        </div>

                        <div class="form-row">

                            <x-input col="4" attr="email" :value="$user->email" label="Email" />

                        </div>

                        <hr>

                        <h3>Configuración</h3>

                        <div class="icheck-primary d-inline">
                            <input type="checkbox" name="pass" id="pass"
                                @if ($user->block == false) checked @endif>
                            <label for="pass">
                                Permitir acceso a la aplicación
                            </label>
                        </div>

                        <br><br>

                        <label>Roles</label>

                        <div class="form-group">
                            <select class="select2bs4" id="roles" name="roles[]"
                                multiple="multiple @error('roles') is-invalid @enderror"
                                data-placeholder="Elige el rol o los roles del usuario" style="width: 100%;">
                                @foreach ($roles as $rol)
                                    <option @if ($user->hasRole($rol->rol)) selected @endif value="{{ $rol->id }}">
                                        {{ $rol->slug }}</option>
                                @endforeach
                            </select>
                            @error('roles')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group" id="committee" style="display: none">
                            <label>Selecciona el comité asociado</label>
                            <select name="committee" class="form-control select2bs4" style="width: 100%;">
                                @foreach ($committees as $committee)
                                    <option @if ($committee->name == $user->associate_committee()) selected @endif value="{{ $committee->id }}">
                                        {{ $committee->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Los roles "COORDINADOR" o "SECRETARIO"
                                exigen tener un comité asociado.</small>
                        </div>



                        <hr>

                        <h3>Cambio de contraseña</h3>

                        <div class="form-row">
                            <x-input col="6" attr="password" :required="false" type="password"
                                label="Nueva contraseña" />
                        </div>

                        <div class="form-row">
                            <x-input col="6" attr="password_confirmation" :required="false" type="password"
                                label="Repite la nueva contraseña" />
                        </div>


                        <div class="form-row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit" class="btn btn-primary btn-block">Actualizar usuario</button>
                            </div>
                        </div>



                    </div>

                </div>

            </div>

            <div class="col-lg-5">

                <div class="row">




                    <div class="col-lg-12">
                        <x-profile :user="$user" />
                    </div>


                </div>



            </div>




        </div>

    </form>

@section('scripts')

    <script>
        var values = $("#roles").val();
        for (var i = 0; i < values.length; i++) {

            // si es coordinador
            if (values[i] == "4") {
                $("#committee").show();
            }

            // si es administrador
            if (values[i] == "5") {
                $("#committee").show();
            }
        }

        $("#roles").change(function() {

            var values = $(this).val();
            var cont = 0;
            for (var i = 0; i < values.length; i++) {

                // si es coordinador
                if (values[i] == "4") {
                    $("#committee").show();
                    cont++;
                }

                // si es administrador
                if (values[i] == "5") {
                    $("#committee").show();
                    cont++;
                }
            }

            if (cont == 0) {
                $("#committee").hide();
            }

        });
    </script>

@endsection

@endsection
