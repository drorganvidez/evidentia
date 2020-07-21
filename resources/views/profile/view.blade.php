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

        <div class="col-md-3">


            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img width="100" height="100" class="img-circle elevation-2"
                             src="{{route('avatar',['instance' => $instance, 'id' => Auth::id()])}}"
                             alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{Auth::user()->name}} {{Auth::user()->surname}}</h3>

                    <x-participation :user="Auth::user()"/>

                    <p class="text-muted text-center">
                        @foreach(Auth::user()->roles as $rol)
                            <span class="badge badge-pill badge-secondary">{{$rol->slug}}</span>
                        @endforeach
                    </p>

                    <p>{!! Auth::user()->biography !!}</p>


                </div>

            </div>


        </div>

        <div class="col-md-9">
            <div class="card">
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

                        <div class="tab-pane" id="timeline">
                            <!-- The timeline -->
                            <div class="timeline timeline-inverse">
                                <!-- timeline time label -->
                                <div class="time-label">
                        <span class="bg-danger">
                          10 Feb. 2014
                        </span>
                                </div>
                                <!-- /.timeline-label -->
                                <!-- timeline item -->
                                <div>
                                    <i class="fas fa-envelope bg-primary"></i>

                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i> 12:05</span>

                                        <h3 class="timeline-header"><a href="#">Support Team</a> sent you an email</h3>

                                        <div class="timeline-body">
                                            Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                                            weebly ning heekya handango imeem plugg dopplr jibjab, movity
                                            jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                                            quora plaxo ideeli hulu weebly balihoo...
                                        </div>
                                        <div class="timeline-footer">
                                            <a href="#" class="btn btn-primary btn-sm">Read more</a>
                                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- END timeline item -->
                                <!-- timeline item -->
                                <div>
                                    <i class="fas fa-user bg-info"></i>

                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i> 5 mins ago</span>

                                        <h3 class="timeline-header border-0"><a href="#">Sarah Young</a> accepted your friend request
                                        </h3>
                                    </div>
                                </div>
                                <!-- END timeline item -->
                                <!-- timeline item -->
                                <div>
                                    <i class="fas fa-comments bg-warning"></i>

                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i> 27 mins ago</span>

                                        <h3 class="timeline-header"><a href="#">Jay White</a> commented on your post</h3>

                                        <div class="timeline-body">
                                            Take me to your leader!
                                            Switzerland is small and neutral!
                                            We are more like Germany, ambitious and misunderstood!
                                        </div>
                                        <div class="timeline-footer">
                                            <a href="#" class="btn btn-warning btn-flat btn-sm">View comment</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- END timeline item -->
                                <!-- timeline time label -->
                                <div class="time-label">
                        <span class="bg-success">
                          3 Jan. 2014
                        </span>
                                </div>
                                <!-- /.timeline-label -->
                                <!-- timeline item -->
                                <div>
                                    <i class="fas fa-camera bg-purple"></i>

                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i> 2 days ago</span>

                                        <h3 class="timeline-header"><a href="#">Mina Lee</a> uploaded new photos</h3>

                                        <div class="timeline-body">
                                            <img src="http://placehold.it/150x100" alt="...">
                                            <img src="http://placehold.it/150x100" alt="...">
                                            <img src="http://placehold.it/150x100" alt="...">
                                            <img src="http://placehold.it/150x100" alt="...">
                                        </div>
                                    </div>
                                </div>
                                <!-- END timeline item -->
                                <div>
                                    <i class="far fa-clock bg-gray"></i>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>


@endsection
