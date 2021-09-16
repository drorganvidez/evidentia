@extends('layouts.app')

@section('title', 'Dashboard')
@section('title-icon', 'fas fa-tachometer-alt')

@section('content')

    <div class="row">

        <div class="col-md-4">

            <div class="row">
                <div class="col-md-12">
                    <!-- Widget: user widget style 1 -->
                    <div class="card card-widget widget-user shadow-lg">
                        <!-- Add the bg color to the header using any of the bg-* classes -->
                        <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}) left;">
                            <h3 class="widget-user-username text-left">{!!Auth::user()->name!!} {!!Auth::user()->surname!!}</h3>
                            <h5 class="widget-user-desc text-left">
                                @foreach(Auth::user()->roles as $role)
                                    {{$role->slug}}
                                @endforeach
                            </h5>
                        </div>
                        <div class="widget-user-image">
                            <img class="img-circle" src="{{Auth::user()->avatar_route()}}" alt="User Avatar">
                        </div>
                        <div class="card-footer">
                            <div class="row mb-4">
                                <div class="col-sm-12">
                                    <div class="description-block mt-0">
                                        <h5 class="description-header" style="font-size: 30px">X</h5>
                                        <span class="description-text" style="font-size: 20px">horas computadas</span>
                                    </div>
                                </div>
                                <div class="col-sm-3 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header" style="font-size: 20px">X</h5>
                                        <span class="description-text" style="font-size: 10px">en evidencias</span>
                                        <br><i class="fas fa-plus-circle" onmouseover="" style="cursor: pointer;" data-toggle="collapse" data-target="#evidencias" aria-expanded="false" aria-controls="collapseExample"></i>

                                    </div>
                                </div>
                                <div class="col-sm-3 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header" style="font-size: 20px">X</h5>
                                        <span class="description-text" style="font-size: 10px">en reuniones</span>
                                        <br><i class="fas fa-plus-circle" onmouseover="" style="cursor: pointer;" data-toggle="collapse" data-target="#reuniones" aria-expanded="false" aria-controls="collapseExample"></i>
                                    </div>
                                </div>
                                <div class="col-sm-3 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header" style="font-size: 20px">X</h5>
                                        <span class="description-text" style="font-size: 10px">en eventos</span>
                                        <br><i class="fas fa-plus-circle" onmouseover="" style="cursor: pointer;" data-toggle="collapse" data-target="#eventos" aria-expanded="false" aria-controls="collapseExample"></i>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="description-block">
                                        <h5 class="description-header" style="font-size: 20px">X</h5>
                                        <span class="description-text" style="font-size: 10px">bonificadas</span>
                                        <br><i class="fas fa-plus-circle" onmouseover="" style="cursor: pointer;" data-toggle="collapse" data-target="#bonos" aria-expanded="false" aria-controls="collapseExample"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="collapse" id="evidencias">
                                        <div class="widget-user">
                                            <div class="card-footer" style="margin-top: 0px; padding-top: 20px;">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="description-block mt-0">
                                                            <span class="description-text" style="font-size: 20px">EVIDENCIAS</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 border-right">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">ACEPTADAS</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 border-right">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">PENDIENTES</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">RECHAZADAS</span>
                                                        </div>
                                                    </div>

                                                </div>
                                                <!-- /.row -->
                                            </div>
                                        </div>
                                        <!-- /.widget-user -->
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="collapse mb-0" id="reuniones">
                                        <div class="widget-user">
                                            <div class="card-footer" style="margin-top: 0px; padding-top: 20px;">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="description-block mt-0">
                                                            <span class="description-text" style="font-size: 20px">REUNIONES</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 border-right">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">ASISTIDAS</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">TOTAL</span>
                                                        </div>
                                                    </div>

                                                </div>
                                                <!-- /.row -->
                                            </div>
                                        </div>
                                        <!-- /.widget-user -->
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="collapse mt-0" id="eventos">
                                        <div class="widget-user">
                                            <div class="card-footer" style="margin-top: 0px; padding-top: 20px;">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="description-block mt-0">
                                                            <span class="description-text" style="font-size: 20px">EVENTOS</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 border-right">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">ASISTIDOS</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">TOTAL</span>
                                                        </div>
                                                    </div>

                                                </div>
                                                <!-- /.row -->
                                            </div>
                                        </div>
                                        <!-- /.widget-user -->
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="collapse mt-0" id="bonos">
                                        <div class="widget-user">
                                            <div class="card-footer" style="margin-top: 0px; padding-top: 20px;">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="description-block mt-0">
                                                            <span class="description-text" style="font-size: 20px">BONOS</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="description-block">
                                                            <h5 class="description-header">X</h5>
                                                            <span class="description-text">HORAS</span>
                                                        </div>
                                                    </div>

                                                </div>
                                                <!-- /.row -->
                                            </div>
                                        </div>
                                        <!-- /.widget-user -->
                                    </div>
                                </div>

                            </div>

                            <!-- /.row -->
                        </div>
                    </div>
                    <!-- /.widget-user -->
                </div>
            </div>

        </div>

        <div class="col-md-8">

            <div class="row">

                <div class="col-lg-12">
                    <div class="timeline" style="margin-left: 10px">
                        <div class="time-label">
                            <span class="bg-default">24 Nov. 2020</span>
                        </div>
                        <div>
                            <i class="fas fa-envelope bg-default"></i>
                            <div class="timeline-item shadow-sm">
                                <span class="time"><i class="fas fa-clock"></i> 12:05</span>
                                <div class="timeline-body">¡Se te ha añadido un bono de horas!</div>
                            </div>
                        </div>
                            <div class="time-label">
                                <span class="bg-default">22 Nov. 2020</span>
                            </div>
                            <div>
                                <i class="fas fa-envelope bg-default"></i>
                                <div class="timeline-item shadow-sm">
                                    <span class="time"><i class="fas fa-clock"></i> 12:05</span>
                                    <h3 class="timeline-header">Notificación</h3>

                                    <div class="timeline-body">
                                        Descripción (si procede)
                                    </div>
                                    <div class="timeline-footer">
                                        footer
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-envelope bg-default"></i>
                                <div class="timeline-item shadow-sm">
                                    <span class="time"><i class="fas fa-clock"></i> 12:05</span>
                                    <h3 class="timeline-header">Notificación</h3>

                                    <div class="timeline-body">
                                        Descripción (si procede)
                                    </div>
                                    <div class="timeline-footer">
                                        footer
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div style=>

                </div>

            </div>

        </div>



    </div>


@endsection
