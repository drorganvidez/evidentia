@extends('layouts.app')

@section('title', 'Gestionar eventos')

@section('title-icon', 'fas fa-calendar-alt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::attendee_timestamp()"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="row mb-3">
                @if(!\Carbon\Carbon::now()->gt(\Config::attendee_timestamp()))
                    <div class="col-lg-3 mt-1">
                        <a href="{{route('registercoordinator.event.load',['instance' => $instance])}}"
                           class="btn btn-primary btn-block" role="button"><i class="fas fa-cloud-download-alt"></i>
                            &nbsp;Cargar eventos desde Eventbrite</a>
                    </div>
                @endif
                <p style="padding: 5px 25px 0px 25px">Exportar tabla:</p>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('registercoordinator.events.export',['ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('registercoordinator.events.export',['ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('registercoordinator.events.export',['ext' => 'pdf'])}}"
                       class="btn btn-info btn-block" role="button">
                        PDF</a>
                </div>
            </div>
            <div class="card shadow-lg">


                <div class="card-body">
                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Descripción</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Fecha de inicio</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Fecha de fin</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Capacidad</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                            <th>Estado</th>
                            <th>Cargar Asistencia</th>
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
                                <td>
                                    <x-eventstatus :event="$event"/>
                                </td>
                                <td><a href="{{route('registercoordinator.attendee.load',['id' => $event->id_eventbrite])}}"
                                    class="btn btn-primary btn-block" role="button">
                                    <i class="fas fa-cloud-download-alt"></i></a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>


        </div>
    </div>

@endsection
