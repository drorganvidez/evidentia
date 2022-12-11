@extends('layouts.app')

@section('title', 'Mis incidencias')

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
                    <a href="{{route('incidence.list.export',['instance' => $instance, 'ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('incidence.list.export',['instance' => $instance, 'ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('incidence.list.export',['instance' => $instance, 'ext' => 'pdf'])}}"
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
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Creada</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Estado</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($incidences as $incidence)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$incidence->id}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <a href="{{route('incidence.view',
                                                ['instance' => $instance, 'id' => $incidence->id])}}">
                                        {{$incidence->title}}
                                    </a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                <x-incidencecomittee :incidence="$incidence"/>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($incidence->created_at)->diffForHumans() }} </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                <x-incidencestatus :incidence="$incidence"/>
                                </td>

                                <td class="align-middle">
                                <x-incidenceoptionsstudent :incidence="$incidence"/>
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


        </div>

    </div>

    @section('scripts')

        <script>

            $(document).ready(function(){
                countdown("{{\Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(Config::upload_incidences_timestamp(),false)}}");
            });

        </script>

    @endsection

@endsection
