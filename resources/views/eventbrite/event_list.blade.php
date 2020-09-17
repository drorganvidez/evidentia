@extends('layouts.app')

@section('title', 'Gestionar eventos')

@section('title-icon', 'fas fa-calendar-alt')

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

            @if(!\Carbon\Carbon::now()->gt(\Config::attendee_timestamp()))
            <div class="row mb-3">
                <div class="col-lg-3 mt-1">
                    <a href="{{route('registercoordinator.event.load',['instance' => $instance])}}" class="btn btn-primary btn-block" role="button"><i class="fas fa-cloud-download-alt"></i> &nbsp;Cargar eventos desde Eventbrite</a>
                </div>
            </div>
            @endif

            <div class="card">


                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Descripción</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Fecha de inicio</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Fecha de fin</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Capacidad</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($events as $event)
                            <tr>
                                <td><a href="{{$event->url}}" target="_blank">{!! $event->name !!}</a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{!! $event->description !!}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ \Carbon\Carbon::parse($event->start_datetime) }}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ \Carbon\Carbon::parse($event->end_datetime) }}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ $event->capacity }}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ $event->hours }}</td>
                                <td><x-eventstatus :event="$event"/></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>


        </div>
    </div>

@endsection
