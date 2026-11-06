@extends('layouts.app')

@section('title', 'Gestionar eventos')

@section('title-icon', 'fas fa-calendar-alt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::attendee_timestamp()" />
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">


                <div class="card-body">

                    <div class="mb-3">
                        <a href="{{ route('registercoordinator.event.load') }}" class="btn btn-success">
                            <i class="fas fa-sync"></i> Cargar eventos desde Eventbrite
                        </a>
                        <a href="{{ route('registercoordinator.event.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Crear evento a mano
                        </a>
                    </div>

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
                            @foreach ($events as $event)
                                <tr>
                                    <td>
                                        @if ($event->url)
                                            <a href="{{ $event->url }}" target="_blank">{!! \Html::clean($event->name) !!}</a>
                                        @else
                                            {!! \Html::clean($event->name) !!}
                                        @endif
                                        @if ($event->id_eventbrite === null)
                                            <br>
                                            <span class="badge badge-info mt-1">Creado a mano</span>
                                        @endif
                                        @if (!empty($event->hidden) && $event->hidden)
                                            <br>
                                            <span class="badge badge-secondary mt-1">Oculto</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{!! \Html::clean($event->description) !!}
                                    </td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                        {{ \Carbon\Carbon::parse($event->start_datetime) }}</td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                        {{ \Carbon\Carbon::parse($event->end_datetime) }}</td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ $event->capacity }}</td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ $event->hours }}</td>
                                    <td>
                                        <x-eventstatus :event="$event" />
                                    </td>
                                    <td class="text-center">
                                        @if ($event->id_eventbrite !== null)
                                            <a href="{{ route('registercoordinator.attendee.load', ['id' => $event->id_eventbrite]) }}"
                                                class="btn btn-primary btn-block mb-1" role="button"
                                                title="Cargar asistencias desde Eventbrite">
                                                <i class="fas fa-cloud-download-alt"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('attendee.create', ['event' => $event->id]) }}"
                                                class="btn btn-primary btn-block mb-1" role="button" title="Añadir asistencia">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                            <a href="{{ route('registercoordinator.event.edit', $event->id) }}"
                                                class="btn btn-secondary btn-block mb-1" role="button" title="Editar evento">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <form action="{{ route('registercoordinator.event.destroy', $event->id) }}"
                                                method="POST" class="mb-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-block" title="Borrar evento">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if (!empty($event->hidden) && $event->hidden)
                                            <form
                                                action="{{ route('registercoordinator.event.unhide', ['id' => $event->id]) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-block"
                                                    title="Mostrar evento">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form
                                                action="{{ route('registercoordinator.event.hide', ['id' => $event->id]) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-block"
                                                    title="Ocultar evento">
                                                    <i class="fas fa-eye-slash"></i>
                                                </button>
                                            </form>
                                        @endif
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
