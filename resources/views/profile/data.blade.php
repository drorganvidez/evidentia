@extends('layouts.app')

@section('title', 'Perfil')

@section('submenu')

    <x-submenus.profile-menu/>

@endsection

@section('content')

    <form method="post" action="{{route('profile.data_p',\Instantiation::instance())}}">

        @csrf

        <div class="row">
            <x-input>
                <x-slot:name>
                    username
                </x-slot:name>
                <x-slot:value>
                    {{Auth::user()->username}}
                </x-slot:value>
                <x-slot:label>
                    Nombre de usuario
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:description>
                    El nombre de usuario no puede ser editado
                </x-slot:description>
                <x-slot:disabled></x-slot:disabled>
            </x-input>
        </div>

        <div class="row">

            <x-input>
                <x-slot:name>
                    name
                </x-slot:name>
                <x-slot:value>
                    {{Auth::user()->name}}
                </x-slot:value>
                <x-slot:label>
                    Nombre
                </x-slot:label>
                <x-slot:col>
                    col-6 col-md-6
                </x-slot:col>
            </x-input>

            <x-input>
                <x-slot:name>
                    surname
                </x-slot:name>
                <x-slot:value>
                    {{Auth::user()->surname}}
                </x-slot:value>
                <x-slot:label>
                    Apellidos
                </x-slot:label>
                <x-slot:col>
                    col-6
                </x-slot:col>
            </x-input>

        </div>

        <div class="row">

            <x-input>
                <x-slot:name>
                    email
                </x-slot:name>
                <x-slot:value>
                    {{Auth::user()->email}}
                </x-slot:value>
                <x-slot:label>
                    Email
                </x-slot:label>
                <x-slot:col>
                    col-12 col-md-6
                </x-slot:col>
                <x-slot:type>
                    email
                </x-slot:type>
            </x-input>

        </div>

        <x-submit>
            <x-slot:name>
                Actualizar
            </x-slot:name>
        </x-submit>

    </form>

    {{--

    <div class="row">

        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#data" data-toggle="tab">Personal</a></li>

                        @if(!Auth::user()->hasRole('LECTURE'))
                            <li class="nav-item"><a class="nav-link" href="#biografia" data-toggle="tab">Resumen de trabajo en jornadas</a></li>
                        @endif

                        <li class="nav-item"><a class="nav-link" href="#pass" data-toggle="tab">Cambiar contraseña</a></li>
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

                                            <x-input col="12" attr="username" :value="Auth::user()->username" disabled="true" :edit="true" label="Uvus" description="El Uvus no puede ser editado."/>

                                            <x-input col="6" attr="name" :value="Auth::user()->name" label="Nombre"/>

                                            <x-input col="6" attr="surname" :value="Auth::user()->surname" label="Apellidos"/>

                                            <x-input col="12" attr="email" :value="Auth::user()->email" label="Email"/>

                                        </div>

                                    </div>

                                    <div class="col-lg-6">

                                        <div class="form-row">

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

    --}}


@endsection
