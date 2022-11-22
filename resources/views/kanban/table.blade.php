@extends('layouts.app')

@section('title', 'Tablero Kanban')

@section('title-icon', 'fas fa-archive')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-4 card card-widget widget-user shadow-sm">

                <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px"><i class="fas fa-briefcase"></i>&nbsp;&nbsp;
                        To do
                    </h5>

                    <h5 class="widget-user-desc text-left">

                    </h5>


                </div>



                <div class="card-footer" style="padding-top: 10px">
                    <div class="row">

                        <div class="col-lg-12">
                            <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                            <p style="margin-bottom: 0px; text-align: justify" class="biography">



                                    <div class="callout callout-danger mt-3">
                                        <h5>Ups...</h5>

                                        <p>Parece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desde <a href="{{route('profile.view',\Instantiation::instance())}}">Mi perfil</a></p>
                                    </div>



                            </p>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-4 card card-widget widget-user shadow-sm">

                <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px"><i class="fas fa-briefcase"></i>&nbsp;&nbsp;
                        In Progress
                    </h5>

                    <h5 class="widget-user-desc text-left">

                    </h5>

                </div>



                <div class="card-footer" style="padding-top: 10px">
                    <div class="row">

                        <div class="col-lg-12">
                            <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                            <p style="margin-bottom: 0px; text-align: justify" class="biography">
                                <div class="callout callout-warning mt-3">
                                    <h5>Ups...</h5>

                                    <p>Parece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                        de implicación en las jornadas desde <a href="{{route('profile.view',\Instantiation::instance())}}">Mi perfil</a></p>
                                </div>
                            </p>
                        </div>
                    </div>
                </div>


            </div>
            <div class="col-4 card card-widget widget-user shadow-sm">

                <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px"><i class="fas fa-briefcase"></i>&nbsp;&nbsp;
                        Closed
                    </h5>

                    <h5 class="widget-user-desc text-left">

                    </h5>


                </div>

                <div class="card-footer" style="padding-top: 10px">
                    <div class="row">

                        <div class="col-lg-12">
                            <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                            <p style="margin-bottom: 0px; text-align: justify" class="biography">



                                    <div class="callout callout-success mt-3">
                                        <h5>Ups...</h5>

                                        <p>Parece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desdeParece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desdeParece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desdeParece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desdeParece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desdeParece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desdeParece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                            de implicación en las jornadas desde <a href="{{route('profile.view',\Instantiation::instance())}}">Mi perfil</a></p>
                                    </div>



                            </p>
                        </div>
                    </div>

                </div>
            </div>
        
        </div>
    </div>



@endsection


