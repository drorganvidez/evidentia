@extends('layouts.app')

@section('title', 'Mis evidencias')

@section('title-icon', 'fas fa-id-badge')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-8">

            <div class="row mb-3">
                <p style="padding: 5px 50px 0px 15px">Exportar tabla:</p>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('evidence.list.export',['instance' => $instance, 'ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('evidence.list.export',['instance' => $instance, 'ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('evidence.list.export',['instance' => $instance, 'ext' => 'pdf'])}}"
                       class="btn btn-info btn-block" role="button">
                        PDF</a>
                </div>
            </div>

            <div class="card shadow-lg">

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
                            <th>Título</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Creada</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Estado</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($evidences as $evidence)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->id}}</td>
                                <td><a href="{{route('evidence.view',['instance' => $instance, 'id' => $evidence->id])}}">{{$evidence->title}}</a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->hours}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-evidencecomittee :evidence="$evidence"/>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-evidencestatus :evidence="$evidence"/>
                                </td>

                                <td class="align-middle">
                                    <x-evidenceoptionsstudent :evidence="$evidence"/>
                                </td>

                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <div class="callout callout-info">
                        <h4>

                            Fecha límite de subidas

                        </h4>

                        <h4>

                            <i class="fas fa-stopwatch"></i>

                            {{\Carbon\Carbon::parse(Config::upload_evidences_timestamp())->format('d/m/Y')}}

                            a las

                            {{\Carbon\Carbon::parse(Config::upload_evidences_timestamp())->format('H:i')}}

                        </h4>

                        <div class="countdown_container" style="display: none">

                            <p>

                                Te quedan

                                <b>
                                    <span id="countdown"></span>
                                </b>

                                para subir evidencias.

                            </p>

                        </div>



                    </div>

                    <div id="accordion">

                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title w-100">
                                    <a class="d-block w-100 collapsed" data-toggle="collapse" href="#acercade" aria-expanded="false">
                                        Acerca de la validación de evidencias
                                    </a>
                                </h4>
                            </div>
                            <div id="acercade" class="collapse" data-parent="#accordion" style="">
                                <div class="card-body">

                                    <p class="text-justify">Todas las evidencias de las jornadas tienen que ser validadas por los respectivos
                                        coordinadores de cada comité.</p>

                                    <p class="text-justify">
                                        Considera que el coordinador de tu comité tiene que valorar tus evidencias y eso
                                        requiere un tiempo.

                                        En caso de que alguna sea rechazada, ten en cuenta que existe un tiempo adicional
                                        desde que modificas tu evidencia y la mandas
                                        hasta que el coordinador la valida.
                                    </p>

                                    <p class="text-justify">
                                        Te recomendamos que no apures al máximo la subida de tus evidencias para que al coordinador
                                        le dé tiempo a subsanar cualquier posible error dentro de plazo.
                                    </p>
                                </div>
                            </div>
                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

    @section('scripts')

        <script>

            $(document).ready(function(){
                countdown("{{\Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(Config::upload_evidences_timestamp(),false)}}");
            });

        </script>

    @endsection

@endsection
