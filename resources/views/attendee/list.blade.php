@extends('layouts.app')

@section('title', 'Mis asistencias')

@section('title-icon', 'fas fa-hiking')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-3 col-sm-12">
            <x-infoattendeeshours :user="Auth::user()" />
        </div>
        <div class="col-lg-3 col-sm-12">
            <x-infoeventtotalcheckedin :user="Auth::user()" />
        </div>
        <div class="col-lg-3 col-sm-12">
            <x-infoeventtotalattending :user="Auth::user()" />
        </div>

    </div>

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Evento</th>
                            <th scope="col">Horas</th>
                            <th scope="col">Fecha de inicio</th>
                            <th scope="col">Fecha de fin</th>
                            <th scope="col">Asistencia</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($attendees as $attendee)
                            <tr scope="row">
                                <td><a href="{{$attendee->event->url}}" target="_blank">{!! $attendee->event->name !!}</a></td>
                                <td>{{$attendee->event->hours}}</td>
                                <td>{{ \Carbon\Carbon::parse($attendee->event->start_datetime) }}</td>
                                <td>{{ \Carbon\Carbon::parse($attendee->event->end_datetime) }}</td>
                                <td><x-attendeestatus :attendee="$attendee"/></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-12">
            Última actualización de eventos: {{ \Carbon\Carbon::parse($events_update)->diffForHumans() }}
            <br>
            Última actualización de asistencias: {{ \Carbon\Carbon::parse($attendees_update)->diffForHumans() }}
        </div>
    </div>

@endsection
