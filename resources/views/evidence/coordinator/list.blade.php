@extends('layouts.app')

@section('title', 'Gestionar evidencias de '.Auth::user()->coordinator->committee->name)

@section('title-icon', 'fas fa-clipboard-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <x-evidencelistcoordinator/>

    <div class="row">

        <div class="col-lg-12 mt-3">

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
                            <th>Título</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Alumna/o</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Recibida</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Estado</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($evidences as $evidence)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->id}}</td>
                                <td>
                                    <a href="{{route('coordinator.evidence.view',
                                                ['id' => $evidence->id])}}">
                                        {{$evidence->title}}
                                    </a>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->user->surname}}
                                    , {{$evidence->user->name}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->hours}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-evidencestatus :evidence="$evidence"/>
                                </td>

                                <td class="align-middle">
                                    <x-evidenceoptionscoordinator :evidence="$evidence"/>
                                </td>

                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

            {{ $evidences->links() }}

        </div>

        <div class="col-lg-6">

            <div class="card">
                <div class="card-body">

                    <div class="alert alert-info">
                        <h4 class="mb-2">
                            <i class="fas fa-stopwatch"></i> Fecha límite para la validación de evidencias
                        </h4>
                        <p class="mb-0">
                            {{ \Carbon\Carbon::parse(\Config::validate_evidences_timestamp())->format('d/m/Y \a \l\a\s H:i') }}
                        </p>
                        <div class="countdown_container mt-2" style="display: none">
                            <p>
                                Te quedan <b><span id="countdown"></span></b> para validar las evidencias de tu comité.
                            </p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h5>Importancia de validar a tiempo</h5>
                        <p class="text-justify">
                            La validación o rechazo de evidencias es clave para cerrar el proceso correctamente. Como coordinador, revisa con antelación para evitar prisas de última hora y posibles errores.
                            <br><br>
                            **No lo dejes para el final**: cuanto antes valides, más margen tendrán tus compañeros/as para subsanar cualquier problema.
                        </p>
                    </div>

                </div>
            </div>


        </div>
    </div>

@section('scripts')

    <script>

        $(document).ready(function () {
            countdown("{{\Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(Config::validate_evidences_timestamp(),false)}}");
        });

    </script>

@endsection

@endsection
