@extends('layouts.app')

@section('title', 'Gestionar reuniones')

@section('title-icon', 'nav-icon far fa-handshake')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card shadow-lg">

                <div class="card-body">
                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th>Reunión</th>
                            <th>Lugar</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Nº de asistentes</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Realizada</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Acta</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($meetings as $meeting)
                            <tr>
                                <td>{{$meeting->title}}</td>
                                <td>{{$meeting->place}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$meeting->hours}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-meetingcomittee :meeting="$meeting"/>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$meeting->users->count()}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ \Carbon\Carbon::parse($meeting->datetime)->diffForHumans() }}</td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="{{route('download.minutes',['instance' => \Instantiation::instance(), 'id' => $meeting->meeting_minutes->id])}}">
                                        <i class="nav-icon nav-icon far fa-file-pdf"></i>
                                    </a>
                                </td>

                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

@endsection
