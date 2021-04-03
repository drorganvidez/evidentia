@extends('layouts.app')

@section('title', 'Gestionar: ' . $user->surname . ', ' . $user->name)

@section('title-icon', 'nav-icon fas fa-cogs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @if(Auth::user()->hasRole('PRESIDENT'))
        <li class="breadcrumb-item"><a href="{{route('president.user.list',$instance)}}">Gestionar alumnos</a></li>
    @else
        <li class="breadcrumb-item"><a href="{{route('lecture.user.list',$instance)}}">Gestionar alumnos</a></li>
    @endif
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <form method="POST" action="{{$route}}">
        @csrf

        <div class="row">


                <div class="col-lg-8">

                    <div class="card shadow-lg">

                        <div class="card-body">

                            <h3>Datos personales</h3>

                            <div class="form-row">

                                <x-input col="6" attr="username" :value="$user->username" label="Uvus"/>

                                <x-input col="6" attr="dni" :value="$user->dni" label="DNI"/>

                            </div>

                            <div class="form-row">

                                <x-input col="6" attr="name" :value="$user->name" label="Nombre"/>

                                <x-input col="6" attr="surname" :value="$user->surname" label="Apellidos"/>

                            </div>

                            <div class="form-row">

                                <x-input col="4" attr="email" :value="$user->email" label="Email"/>

                            </div>

                            <hr>

                            <h3>Cambio de contraseña</h3>

                            <div class="form-row">
                                <x-input col="6" attr="password" :required="false" type="password" label="Nueva contraseña"/>
                            </div>

                            <div class="form-row">
                                <x-input col="6" attr="password_confirmation" :required="false" type="password" label="Repite la nueva contraseña"/>
                            </div>

                            <div class="form-row">
                                <div class="col-lg-3 mt-1">
                                    <button type="submit"  class="btn btn-primary btn-block">Actualizar usuario</button>
                                </div>
                            </div>



                        </div>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="row">




                        <div class="col-lg-12">
                            <x-profile :user="$user"/>
                        </div>

                        <div class="col-lg-12">

                            <div class="card shadow-sm">

                                <div class="card-body">

                                    <h3>Opciones</h3>

                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" name="block" id="block" @if($user->block == false) checked @endif>
                                        <label for="block">
                                            Permitir acceso a la aplicación
                                        </label>
                                    </div>

                                    <br><br>

                                    <label>Roles</label>

                                    <div class="form-group">
                                        <select class="select2bs4" id="roles" name="roles[]" multiple="multiple @error('roles') is-invalid @enderror" data-placeholder="Elige el rol o los roles del usuario"
                                                style="width: 100%;">
                                            @foreach($roles as $rol)
                                                <option
                                                    @if($user->hasRole($rol->rol))
                                                    selected
                                                    @endif
                                                    value="{{$rol->id}}"
                                                >{{$rol->slug}}</option>
                                            @endforeach
                                        </select>
                                        @error('roles')
                                        <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group" id="comittee" style="display: none">
                                        <label>Selecciona el comité asociado</label>
                                        <select name="comittee" class="form-control select2bs4" style="width: 100%;">
                                            @foreach($comittees as $comittee)
                                                <option
                                                    @if($comittee->name == $user->associate_comittee())
                                                    selected
                                                    @endif
                                                    value="{{$comittee->id}}"
                                                >{{$comittee->name}}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Los roles "COORDINADOR" o "SECRETARIO"
                                            exigen tener un comité asociado.</small>
                                    </div>

                                </div>



                            </div>

                        </div>
                    </div>



                </div>




        </div>

    </form>

    <div class="row">

        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">

                    <form method="POST" action="{{$route}}">
                        @csrf

                        <x-id :id="$user->id ?? ''" :edit="true"/>

                        <div class="row">

                            <div class="col-md-6">

                                <x-profile :user="$user"/>

                            </div>

                            <div class="col-lg-6">
                                <div class="callout callout-info">
                                    <h5>Opciones del perfil</h5>

                                    <div class="form-row">

                                        <div class="col-md-12 form-group">
                                            <div class="custom-control custom-switch custom-switch-on-danger custom-switch-off-success">
                                                <input type="checkbox"

                                                       @if($user->block == true)
                                                           checked
                                                           @endif

                                                       name="block" class="custom-control-input" id="customSwitch3">
                                                <label class="custom-control-label" for="customSwitch3">Permitir acceso del usuario a Evidentia</label>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>


                        </div>

                        <div class="row">

                            {{-- SOLO UN PROFESOR PUEDE CAMBIAR LOS DATOS PERSONALES DE UN USUARIO --}}
                            @if(Auth::user()->hasRole('LECTURE'))
                            <div class="col-lg-6">
                                <div class="callout callout-info">
                                    <h5>Datos personales</h5>

                                    <div class="form-row">

                                        <x-input col="4" attr="username" :value="$user->username" label="Uvus"/>

                                        <x-input col="4" attr="dni" :value="$user->dni" label="DNI"/>

                                    </div>

                                    <div class="form-row">

                                        <x-input col="4" attr="name" :value="$user->name" label="Nombre"/>

                                        <x-input col="4" attr="surname" :value="$user->surname" label="Apellidos"/>



                                    </div>

                                    <div class="form-row">
                                        <x-input col="4" attr="email" :value="$user->email" label="Email"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="callout callout-info">
                                    <h5>Cambio de contraseña</h5>

                                    <div class="form-row">
                                        <x-input col="6" attr="password" :required="false" type="password" label="Nueva contraseña"/>
                                    </div>

                                    <div class="form-row">
                                        <x-input col="6" attr="password_confirmation" :required="false" type="password" label="Repite la nueva contraseña"/>
                                    </div>

                                </div>


                            </div>
                            @endif



                        </div>
                        <div class="form-row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit"  class="btn btn-primary btn-block">Actualizar usuario</button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

    @section('scripts')

        <script>

            var values = $("#roles").val();
            for (var i = 0; i < values.length; i++) {

                // si es coordinador
                if(values[i] == "4"){
                    $("#comittee").show();
                }

                // si es administrador
                if(values[i] == "5"){
                    $("#comittee").show();
                }
            }

            $("#roles").change(function(){

                var values = $(this).val();
                var cont = 0;
                for (var i = 0; i < values.length; i++) {

                    // si es coordinador
                    if(values[i] == "4"){
                        $("#comittee").show();
                        cont++;
                    }

                    // si es administrador
                    if(values[i] == "5"){
                        $("#comittee").show();
                        cont++;
                    }
                }

                if(cont == 0){
                    $("#comittee").hide();
                }

            });

        </script>

    @endsection

@endsection
