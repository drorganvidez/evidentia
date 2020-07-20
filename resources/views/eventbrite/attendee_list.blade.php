@extends('layouts.app')

@section('title', 'Gestionar asistencias')

@section('title-icon', 'fas fa-user-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::attendee_timestamp()"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="row mb-3">
                <div class="col-lg-4 mt-1">
                    <a href="{{route('registercoordinator.attendee.load',['instance' => $instance])}}"
                       class="btn btn-primary btn-block" role="button">
                        <i class="fas fa-cloud-download-alt"></i> &nbsp;Cargar asistencias desde Eventbrite</a>
                </div>
            </div>

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Alumna/o</th>
                            <th scope="col">Evento</th>
                            <th scope="col">Asistencia</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($attendees as $attendee)
                            <tr scope="row">
                                <td>{{$attendee->user->surname}}, {{$attendee->user->name}}</td>
                                <td>{{$attendee->event->name}}</td>
                                <td><x-attendeestatus :attendee="$attendee"/></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

@endsection
