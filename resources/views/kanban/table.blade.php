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
                                            <a class="btn btn-info btn-sm"
                                                href="{{route('kanban.issue_todo_inprogress',['instance' => $instance, 'id' => $issue->id, 'issuesToDo' => $issuesToDo, 'issuesInProgress' => $issuesInProgress, 'issuesClosed' => $issuesClosed])}}">
                                                    <i class="fas fa-pencil-alt">
                                                    </i>
                                            </a>
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
                        In Progress
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
                        Closed
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


