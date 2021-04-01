@extends('layouts.app')

@section('title', 'Gestionar reuniones')

@section('title-icon', 'far fa-list-alt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            @if(!\Carbon\Carbon::now()->gt(\Config::meetings_timestamp()))
            <div class="row mb-3">
                <div class="col-lg-2 mt-1">
                    <a href="{{route('secretary.meeting.create',['instance' => $instance])}}" class="btn btn-primary btn-block" role="button"><i class="fas fa-plus"></i> &nbsp;Crear nueva reunión</a>
                </div>
            </div>
            @endif

            <div class="card">


                <div class="card-body">
                    <table id="dataset" class="table table-hover">
                            <thead>
                            <tr>
                                <th>Reunión</th>
                                <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Lugar</th>
                                <th>Horas</th>
                                <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Realizada</th>
                                @if(!\Carbon\Carbon::now()->gt(\Config::meetings_timestamp()))
                                <th>Herramientas</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($meetings as $meeting)
                                <tr>
                                    <td>{{$meeting->title}}</td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$meeting->place}}</td>
                                    <td>{{$meeting->hours}}</td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ \Carbon\Carbon::parse($meeting->datetime)->diffForHumans() }}</td>
                                    @if(!\Carbon\Carbon::now()->gt(\Config::meetings_timestamp()))
                                    <td>
                                        <a class="btn btn-primary btn-sm"
                                           href="{{route('secretary.meeting.edit',['instance' => $instance, 'id' => $meeting->id])}}"
                                           role="button">
                                            <i class="far fa-edit"></i>
                                            <span class="d-none d-sm-none d-md-none d-lg-inline">Editar reunión</span>
                                        </a>

                                        <x-buttonconfirm :id="$meeting->id" route="secretary.meeting.remove" title="¿Seguro?" description="La reunión y las actas asociadas se borrarán. Las horas asociadas a los alumnos también." type="REMOVE" />

                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>

        </div>


    </div>


@endsection
