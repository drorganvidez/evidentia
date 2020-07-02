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



            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{asset('dist/img/user2-160x160.jpg')}}"
                             alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{Auth::user()->name}} {{Auth::user()->surname}}</h3>

                    <p class="text-muted text-center">
                        @foreach(Auth::user()->roles as $rol)
                            <span class="badge badge-pill badge-secondary">{{$rol->slug}}</span>
                        @endforeach
                    </p>

                    <p style="text-justify: auto">{!! Auth::user()->biography !!}</p>


                </div>

            </div>


        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#data" data-toggle="tab">Personal</a></li>
                        <li class="nav-item"><a class="nav-link" href="#biografia" data-toggle="tab">Biografía</a></li>
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

                                            <x-input col="12" attr="username" :value="Auth::user()->username" disabled="true" :edit="true" label="Nombre de usuario"/>

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

                        <div class="tab-pane" id="biografia">
                            <form method="POST" enctype="multipart/form-data" action="{{$route_upload_biography}}">
                            @csrf

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
