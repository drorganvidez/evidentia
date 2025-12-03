@extends('layouts.app')

@section('title', 'Gestionar asistencias')

@section('title-icon', 'fas fa-user-check')

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

            <div class="row mb-3">
                <div class="col-lg-6">

                    <a href="{{ route('registercoordinator.attendee.export') }}" class="btn btn-info me-2">
                        <i class="fas fa-file-export"></i>
                        Exportar asistencias
                    </a>

                    <a href="{{ route('attendee.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus"></i>
                        Añadir asistencia
                    </a>

                </div>
            </div>


            <div class="card">

                <div class="card-body">



                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                            <tr>
                                <th scope="col">Alumna/o</th>
                                <th scope="col">Evento</th>
                                <th scope="col">Asistencia</th>
                                <th scope="col">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendees as $attendee)
                                <tr scope="row">
                                    <td>{{ $attendee->user->surname }}, {{ $attendee->user->name }}</td>
                                    <td><a href="{{ $attendee->event->url }}" target="_blank">{!! $attendee->event->name !!}</a>
                                    </td>
                                    <td><x-attendeestatus :attendee="$attendee" /></td>
                                    <td>
                                        <a href="{{ route('attendee.edit', $attendee->id) }}"
                                            class="btn btn-sm btn-warning">
                                            Editar
                                        </a>

                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteAttendeeModal" data-attendee-id="{{ $attendee->id }}">
                                            Borrar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

    <div class="modal fade" id="deleteAttendeeModal" tabindex="-1" role="dialog" aria-labelledby="deleteAttendeeLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAttendeeLabel">Eliminar asistencia</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    ¿Seguro que quieres eliminar esta asistencia? Esta acción no se puede deshacer.
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <form id="deleteAttendeeForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            Sí, eliminar
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#deleteAttendeeModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var attendeeId = button.data('attendee-id');

                var url = '{{ route('attendee.destroy', ':id') }}';
                url = url.replace(':id', attendeeId);

                $('#deleteAttendeeForm').attr('action', url);
            });
        });
    </script>

@endsection
