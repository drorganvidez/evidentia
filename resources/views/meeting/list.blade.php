@extends('layouts.app')

@section('title', 'Gestionar reuniones')

@section('title-icon', 'far fa-list-alt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::meetings_timestamp()"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="row mb-3">
                <div class="col-lg-2 mt-1">
                    <a href="{{route('secretary.meeting.create',['instance' => $instance])}}" class="btn btn-primary btn-block" role="button"><i class="fas fa-plus"></i> &nbsp;Crear nueva reunión</a>
                </div>
            </div>

            <div class="card">


                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Reunión</th>
                                <th scope="col">Lugar</th>
                                <th scope="col">Horas</th>
                                <th scope="col">Realizada</th>
                                <th scope="col">Herramientas</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($meetings as $meeting)
                                <tr scope="row">
                                    <td>{{$meeting->title}}</td>
                                    <td>{{$meeting->place}}</td>
                                    <td>{{$meeting->hours}}</td>
                                    <td>{{ \Carbon\Carbon::parse($meeting->datetime)->diffForHumans() }}</td>
                                    <td>
                                        <a class="btn btn-primary btn-sm"
                                           href="{{route('secretary.meeting.edit',['instance' => $instance, 'id' => $meeting->id])}}"
                                           role="button">
                                            <i class="far fa-edit"></i>
                                            Editar reunión</a>

                                        <x-buttonconfirm :id="$meeting->id" route="secretary.meeting.remove" title="¿Seguro?" description="La reunión y las actas asociadas se borrarán. Las horas asociadas a los alumnos también." type="REMOVE" />

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
