@extends('layouts.app')

@section('title', 'Mi perfil')
@section('title-icon', 'nav-icon fas fa-user')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">
            <x-status/>
        </div>

        <div class="col-md-4">

            <x-profile :user="Auth::user()"/>

        </div>

        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#data" data-toggle="tab">Personal</a></li>

                        @if(!Auth::user()->hasRole('LECTURE'))
                            <li class="nav-item"><a class="nav-link" href="#biografia" data-toggle="tab">Biografía</a></li>
                        @endif

                        <li class="nav-item"><a class="nav-link" href="#pass" data-toggle="tab">Contraseña</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        <div class="active tab-pane" id="data">
                            <form method="POST" enctype="multipart/form-data" action="{{$route_upload_info}}">
                            @csrf

                                <div class="form-row">

                                    <div class="col-lg-6">

                                        <div class="form-row">

                                            <x-input col="6" attr="username" :value="Auth::user()->username" disabled="true" :edit="true" label="Uvus" description="El Uvus no puede ser editado."/>

                                            <x-input col="6" attr="dni" :value="Auth::user()->dni" disabled="true" :edit="true" label="DNI" description="El DNI no puede ser editado."/>

                                            <x-input col="6" attr="name" :value="Auth::user()->name" label="Nombre"/>

                                            <x-input col="6" attr="surname" :value="Auth::user()->surname" label="Apellidos"/>

                                            <x-input col="12" attr="email" :value="Auth::user()->email" label="Email"/>

                                        </div>

                                    </div>

                                    <div class="col-lg-6">

                                        <div class="form-row">

                                            <x-input col="12" attr="avatar" id="files" type="file" :required="false" label="Avatar" description="Cambia la imagen de avatar por defecto."/>

                                        </div>

                                    </div>

                                </div>

                                <div class="form-row">
                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-primary btn-block" data-dismiss="modal">Actualizar perfil</button>
                                    </div>
                                </div>



                            </form>
                        </div>

                        @if(!Auth::user()->hasRole('LECTURE'))
                        <div class="tab-pane" id="biografia">
                            <form method="POST" enctype="multipart/form-data" action="{{$route_upload_biography}}">
                            @csrf

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="participation">Nivel de participación</label>
                                        <select id="participation" class="selectpicker form-control @error('participation') is-invalid @enderror" name="participation" value="{{ old('participation') }}" required autofocus>

                                            <option {{old('participation') == 1 || Auth::user()->participation == 'ORGANIZATION' ? 'selected' : 'NO'}} value="1">ORGANIZACIÓN</option>
                                            <option {{old('participation') == 2 || Auth::user()->participation == 'INTERMEDIATE' ? 'selected' : 'NO'}} value="2">INTERMEDIO</option>
                                            <option {{old('participation') == 3 || Auth::user()->participation == 'ASSISTANCE' ? 'selected' : 'NO'}} value="3">ASISTENCIA</option>

                                        </select>

                                        <small class="form-text text-muted">Selecciona el nivel de participación en las jornadas InnoSoft Days de este año.</small>

                                        @error('participation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-row">

                                    <x-textarea col="12" attr="biography" :value="Auth::user()->biography ?? ''"
                                                label="Resumen de trabajo en jornadas"
                                                description="Recuerda completar tu información referente a tu trabajo en las Jornadas InnoSoft
                                    para la evaluación de este año."
                                    />
                                </div>

                                <div class="form-row">
                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-primary btn-block" data-dismiss="modal">Actualizar resumen</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                        @endif
                        <div class="tab-pane" id="pass">
                            <form method="POST" enctype="multipart/form-data" action="{{$route_upload_pass}}">
                                @csrf

                                <div class="form-row">
                                    <x-input col="6" attr="password" type="password" label="Nueva contraseña"/>
                                </div>

                                <div class="form-row">
                                    <x-input col="6" attr="password_confirmation" type="password" label="Repite la nueva contraseña"/>
                                </div>

                                <div class="form-row">
                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-primary btn-block" data-dismiss="modal">Actualizar contraseña</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>


@endsection
