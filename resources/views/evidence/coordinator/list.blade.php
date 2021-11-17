@extends('layouts.app')

@section('title', 'Gestionar evidencias de '.Auth::user()->coordinator->comittee->name)

@section('title-icon', 'fas fa-clipboard-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <x-evidencelistcoordinator/>
    <br>
    <div class="row mb-3">
        <p style="padding: 5px 25px 0px 15px">Exportar tabla:</p>
        <div class="col-lg-1 mt-12">
            <a href="{{route('coordinator.evidence.export',['instance' => $instance, 'type' => $type, 'ext' => 'xlsx'])}}"
               class="btn btn-info btn-block" role="button">
                XLSX</a>
        </div>
        <div class="col-lg-1 mt-12">
            <a href="{{route('coordinator.evidence.export',['instance' => $instance, 'type' => $type, 'ext' => 'csv'])}}"
               class="btn btn-info btn-block" role="button">
                CSV</a>
        </div>
        <div class="col-lg-1 mt-12">
            <a href="{{route('coordinator.evidence.export',['instance' => $instance, 'type' => $type, 'ext' => 'pdf'])}}"
               class="btn btn-info btn-block" role="button">
                PDF</a>
        </div>
    </div>
    <div class="row">

        <div class="col-lg-8 mt-3">

            <div class="card shadow-lg">

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
                                                ['instance' => $instance, 'id' => $evidence->id])}}">
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

        <div class="col-lg-4 mt-3">

            <div class="card shadow-sm">

                <div class="card-body">

                    <div class="callout callout-info">
                        <h4>

                            Fecha límite para la validación de evidencias

                        </h4>

                        <h4>

                            <i class="fas fa-stopwatch"></i>

                            {{\Carbon\Carbon::parse(Config::validate_evidences_timestamp())->format('d/m/Y')}}

                            a las

                            {{\Carbon\Carbon::parse(Config::validate_evidences_timestamp())->format('H:i')}}

                        </h4>

                        <div class="countdown_container" style="display: none">

                            <p>

                                Quedan

                                <b>
                                    <span id="countdown"></span>
                                </b>

                                para validar las evidencias de tu comité.

                            </p>

                        </div>

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
