@extends('layouts.app')

@section('title', 'Mis evidencias')

@section('title-icon', 'fas fa-id-badge')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

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
                                <td><a href="{{route('evidence.view',['id' => $evidence->id])}}">{{$evidence->title}}</a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->hours}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-evidencecommittee :evidence="$evidence"/>
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

        <div class="col-lg-6">

 <div class="card">
    <div class="card-body">

        <div class="alert alert-info">
            <h4 class="mb-2">
                <i class="fas fa-stopwatch"></i> Fecha límite para subir evidencias
            </h4>
            <p class="mb-0">
                {{ \Carbon\Carbon::parse(\Config::upload_evidences_timestamp())->format('d/m/Y \a \l\a\s H:i') }}
            </p>
            <div class="countdown_container mt-2" style="display: none">
                <p>
                    Te quedan <b><span id="countdown"></span></b> para subir evidencias.
                </p>
            </div>
        </div>

        <div class="mt-3">
            <h5>Acerca de la validación de evidencias</h5>
            <p class="text-justify">
                Todas las evidencias deben ser validadas por los coordinadores de comité. Este proceso lleva tiempo, especialmente si alguna es rechazada y debes modificarla.
                <br><br>
                Por eso, **no apures el plazo**: subir las evidencias con antelación facilita que los coordinadores las revisen y corrijan dentro del tiempo establecido.
            </p>
        </div>

    </div>
</div>


        </div>

    </div>

    @section('scripts')

        <script>

            $(document).ready(function(){
                countdown("{{\Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(\Config::upload_evidences_timestamp(),false)}}");
            });

        </script>

    @endsection

@endsection
