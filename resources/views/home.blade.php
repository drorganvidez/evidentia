@extends('layouts.app')

@section('title', 'Dashboard')
@section('title-icon', 'fas fa-tachometer-alt')

@section('content')

    <div class="row">

        <div class="col-md-4">

            <div class="row">
                <div class="col-md-12">

                    <x-profile :user="Auth::user()"></x-profile>

                </div>
            </div>

        </div>

        <div class="col-md-8">

            <div class="row">

                <div class="col-lg-6">

                    <div class="row">

                        <div class="col-lg-12">

                            <div class="card card-widget widget-user shadow-sm">

                                <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px">
                                        <i class="fas fa-child"></i> <i class="fas fa-scroll"></i>
                                        &nbsp;&nbsp;
                                        Convocatorias y actas de reunión
                                    </h5>

                                </div>

                                <div class="card-footer" style="padding-top: 10px">
                                    <div class="row">

                                        <div class="col-lg-12 mt-2">
                                            <span class="description-text" style="font-size: 20px; text-align: left;"></span>

                                            <div id="accordion">

                                                @foreach(\App\Models\Comittee::all() as $committee)
                                                    <div class="card card-light">
                                                        <div class="card-header">
                                                            <h4 class="card-title w-100">
                                                                <a class="d-block w-100 collapsed" data-toggle="collapse" href="#{{$committee->name}}" aria-expanded="false">
                                                                    {{$committee->name}}
                                                                </a>
                                                            </h4>
                                                        </div>
                                                        <div id="{{$committee->name}}" class="collapse" data-parent="#accordion" style="">
                                                            <div class="card-body">

                                                                <h6><b>Convocatorias</b></h6>

                                                                <h6><b>Actas</b></h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach


                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">

                            <div class="card card-widget widget-user shadow-sm">

                                <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px">
                                        <i class="fas fa-calendar-day"></i>
                                        &nbsp;&nbsp;
                                        Eventos programados
                                    </h5>

                                </div>

                                <div class="card-footer" style="padding-top: 10px">
                                    <div class="row">

                                        <div class="col-lg-12">
                                            <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                                            <p style="margin-bottom: 0px; text-align: justify" class="biography">

                                                Bla bla bla
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="card card-widget widget-user shadow-sm">

                        <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">
                            <h5 class="widget-user-desc text-left" style="margin-bottom: 0px">
                                <i class="fas fa-clock"></i>
                                &nbsp;&nbsp;
                                Línea temporal
                            </h5>
                        </div>

                        <div class="card-footer" style="padding-top: 10px">

                            <div class="timeline" style="margin-left: 10px">
                                <div class="time-label">
                                    <span class="bg-default shadow-sm">24 Nov. 2020</span>
                                </div>
                                <div>
                                    <i class="fas fa-envelope bg-default"></i>
                                    <div class="timeline-item shadow-sm">
                                        <span class="time"><i class="fas fa-clock"></i> 12:05</span>
                                        <div class="timeline-body">¡Se te ha añadido un bono de horas!</div>
                                    </div>
                                </div>
                                <div class="time-label">
                                    <span class="bg-default shadow-sm">22 Nov. 2020</span>
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

        </div>



    </div>


@endsection
