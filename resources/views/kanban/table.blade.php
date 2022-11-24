<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css%22%3E">
<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
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
                        Asignadas
                    </h5>

                    <h5 class="widget-user-desc text-left">

                    </h5>


                </div>



                <div class="card-footer" style="padding-top: 10px">
                    @foreach($issuesToDo as $issue)
                        <div class="row">
                            <div class="col-lg-12">
                                <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                                <p style="margin-bottom: 0px; text-align: justify" class="biography">



                                        <div class="callout callout-danger mt-3">
                                            <h5>{{$issue->task}}</h5>

                                            <p>{{$issue->description}}</p>
                                            <p>Asignado a: {{$issue->user->name}} {{$issue->user->surname}}</p>
                                            <p>Horas estimadas: {{$issue->hours}}h</p>
                                            @if($issue->user_id == Auth::user()->id)
                                                <a href="{{route('kanban.issueinprogress',['instance' => $instance, 'id' => $issue->id])}}">
                                                    Comenzar tarea.
                                                </a>
                                            @endif
                                        </div>

                                </p>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
            <div class="col-4 card card-widget widget-user shadow-sm">

                <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px"><i class="fas fa-briefcase"></i>&nbsp;&nbsp;
                        En curso
                    </h5>

                    <h5 class="widget-user-desc text-left">

                    </h5>

                </div>



                <div class="card-footer" style="padding-top: 10px">

                    @foreach($issuesInProgress as $issue)
                        <div class="row">
                            <div class="col-lg-12">
                                <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                                <p style="margin-bottom: 0px; text-align: justify" class="biography">
                                    <div class="callout callout-warning mt-3">
                                        <h5>{{$issue->task}}</h5>

                                        <p>{{$issue->description}}</p>
                                        <p>Asignado a: {{$issue->user->name}} {{$issue->user->surname}}</p>
                                        <p>Horas estimadas: {{$issue->hours}}h</p>
                                        @if($issue->user_id == Auth::user()->id)
                                            <a href="{{route('kanban.issueclosed',['instance' => $instance, 'id' => $issue->id])}}">
                                                Terminar tarea.    
                                            </a>
                                        @endif
                                    </div>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>


            </div>
            <div class="col-4 card card-widget widget-user shadow-sm">

                <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px"><i class="fas fa-briefcase"></i>&nbsp;&nbsp;
                        Cerradas
                    </h5>

                    <h5 class="widget-user-desc text-left">

                    </h5>


                </div>

                <div class="card-footer" style="padding-top: 10px">
                    @foreach($issuesClosed as $issue)
                        <div class="row">

                            <div class="col-lg-12">
                                <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                                <p style="margin-bottom: 0px; text-align: justify" class="biography">



                                        <div class="callout callout-success mt-3">
                                            <h5>{{$issue->task}}</h5>

                                            <p>{{$issue->description}}</p>
                                            <p>Asignado a: {{$issue->user->name}} {{$issue->user->surname}}</p>
                                            <p>Horas estimadas: {{$issue->hours}}h</p>
                                        </div>



                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        
        </div>
    </div>



@endsection


