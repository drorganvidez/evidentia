@extends('layouts.app')

@section('title', 'Mis reuniones')

@section('title-icon', 'nav-icon fas fa-cocktail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                            <tr>
                                <th>Reunión</th>
                                <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Lugar</th>
                                <th>Horas</th>
                                <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Realizada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($meetings as $meeting)
                                <tr scope="row">
                                    <td>{{ $meeting->title }}</td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ $meeting->place }}</td>
                                    <td>{{ $meeting->hours }}</td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                        {{ \Carbon\Carbon::parse($meeting->datetime)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>



                </div>

            </div>


        </div>

        <div class="col-lg-6">

            <div class="card">
                <div class="card-body">

                    <div class="alert alert-info mb-0">
                        <h4 class="mb-2">
                            <i class="fas fa-stopwatch"></i> Fecha límite para registrar reuniones
                        </h4>
                        <p class="mb-0">
                            {{ \Carbon\Carbon::parse(Config::meetings_timestamp())->format('d/m/Y \a \l\a\s H:i') }}
                        </p>
                        <div class="countdown_container mt-2" style="display: none">
                            <p>
                                Quedan <b><span id="countdown"></span></b> para registrar reuniones en Evidentia.
                            </p>
                        </div>
                    </div>

                </div>
            </div>


        </div>


    </div>

@section('scripts')

    <script>
        $(document).ready(function() {
            countdown(
                "{{ \Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(Config::meetings_timestamp(), false) }}"
            );
        });
    </script>

@endsection

@endsection
