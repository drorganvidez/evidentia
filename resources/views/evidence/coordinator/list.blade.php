@extends('layouts.app')

@section('title', 'Gestionar evidencias de '.Auth::user()->coordinator->comittee->name)

@section('title-icon', 'fas fa-clipboard-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row mb-2">
        <div class="form-group col-md-6 col-sm-6">
            <form method="GET" action="{{route('coordinator.evidence.list.search',$instance)}}" />
            <input name="s" type="text" class="form-control" placeholder="Buscar evidencia por título, alumna/o, horas..." name="title" value="" required=""
                   autocomplete="title" autofocus>
        </div>
        <div class="col-md-2 col-sm-6">
            <button type="button" class="btn btn-primary btn-block"><i class="fas fa-search"></i> &nbsp;Buscar</button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-2 mt-1">
            <a  class="btn btn-primary btn-block" disabled><i class="fas fa-clock"></i> &nbsp;Pendientes</a>
        </div>
        <div class="col-lg-2 mt-1">
            <a type="button" class="btn btn-secondary btn-block"><i class="far fa-thumbs-up"></i> &nbsp;Aceptadas</a>
        </div>
        <div class="col-lg-2 mt-1">
            <a type="button" class="btn btn-secondary btn-block"><i class="far fa-thumbs-down"></i> &nbsp;Rechazadas</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mt-3">

            <x-status/>

            <div class="card">

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap ">
                        <thead>
                        <tr>
                            <th>Alumna/o</th>
                            <th>Título</th>
                            <th>Horas</th>
                            <th>Recibida</th>
                            <th>Estado</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($evidences as $evidence)
                            <tr>
                                <td>{{$evidence->user->surname}}, {{$evidence->user->name}}</td>
                                <td>
                                    <a href="{{route('evidence.view',
                                                ['instance' => $instance, 'id' => $evidence->id])}}">
                                        {{$evidence->title}}
                                    </a>
                                </td>
                                <td>{{$evidence->hours}}</td>
                                <td> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
                                <td>
                                    <x-evidencestatus :status="$evidence->status"/>
                                </td>

                                <td>
                                    <x-evidenceoptionscoordinator :evidence="$evidence"/>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td>Sin evidencias
                            </tr>
                        @endforelse

                        </tbody>
                    </table>

                </div>

            </div>

            {{ $evidences->links() }}

        </div>
    </div>

@endsection
