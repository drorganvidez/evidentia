@extends('layouts.app')

@section('title', $event->exists ? 'Editar evento' : 'Crear evento')

@section('title-icon', 'fas fa-calendar-plus')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('registercoordinator.event.list') }}">Gestionar eventos</a>
    </li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::attendee_timestamp()" />
@endsection

@section('content')

    @php
        $start = $event->start_datetime ? \Carbon\Carbon::parse($event->start_datetime)->format('Y-m-d\TH:i') : '';
        $end = $event->end_datetime ? \Carbon\Carbon::parse($event->end_datetime)->format('Y-m-d\TH:i') : '';
    @endphp

    <div class="card">
        <div class="card-body">

            <form method="POST"
                action="{{ $event->exists ? route('registercoordinator.event.update', $event->id) : route('registercoordinator.event.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input id="name" type="text" name="name" maxlength="255" required
                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $event->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="start_datetime">Inicio</label>
                        <input id="start_datetime" type="datetime-local" name="start_datetime" required
                            class="form-control @error('start_datetime') is-invalid @enderror"
                            value="{{ old('start_datetime', $start) }}">
                        @error('start_datetime')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="end_datetime">Fin</label>
                        <input id="end_datetime" type="datetime-local" name="end_datetime" required
                            class="form-control @error('end_datetime') is-invalid @enderror"
                            value="{{ old('end_datetime', $end) }}">
                        @error('end_datetime')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="capacity">Capacidad</label>
                    <input id="capacity" type="number" name="capacity" min="0"
                        class="form-control @error('capacity') is-invalid @enderror"
                        value="{{ old('capacity', $event->capacity) }}">
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" rows="3" maxlength="5000"
                        class="form-control @error('description') is-invalid @enderror">{{ old('description', $event->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary">{{ $event->exists ? 'Guardar evento' : 'Crear evento' }}</button>
                <a href="{{ route('registercoordinator.event.list') }}" class="btn btn-secondary">Cancelar</a>

            </form>

        </div>
    </div>

@endsection
