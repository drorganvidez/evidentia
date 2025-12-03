@extends('layouts.app')

@section('title', 'Editar asistencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('registercoordinator.attendee.list') }}">Gestionar asistencias</a>
    </li>
    <li class="breadcrumb-item">@yield('title')</li>
@endsection

@section('content')

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('attendee.update', $attendee->id) }}">
                @csrf

                <div class="form-group">
                    <label>Usuario</label>
                    <select name="user_id" class="form-control">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @if ($attendee->user_id == $user->id) selected @endif>
                                {{ $user->surname }} {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label>Evento</label>
                    <select name="event_id" class="form-control">
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @if ($attendee->event_id == $event->id) selected @endif>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label>Estado</label>
                    <select name="status" class="form-control">
                        <option value="Checked In" @selected($attendee->status == 'Checked In')>
                            Asistido
                        </option>

                        <option value="Not Attending" @selected($attendee->status == 'Not Attending')>
                            No asistido
                        </option>
                    </select>
                </div>

                <button class="btn btn-success mt-3">Guardar cambios</button>

            </form>

        </div>
    </div>

@endsection
