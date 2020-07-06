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

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Reunión</th>
                            <th scope="col">Lugar</th>
                            <th scope="col">Horas</th>
                            <th scope="col">Comité</th>
                            <th scope="col">Nº de asistentes</th>
                            <th scope="col">Realizada</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($meetings as $meeting)
                            <tr>
                                <td>{{$meeting->title}}</td>
                                <td>{{$meeting->place}}</td>
                                <td>{{$meeting->hours}}</td>
                                <td>
                                    <x-meetingcomittee :meeting="$meeting"/>
                                </td>
                                <td>{{$meeting->users->count()}}</td>
                                <td>{{ \Carbon\Carbon::parse($meeting->datetime)->diffForHumans() }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

@endsection
