@extends('layouts.app')

@section('title', 'Gestionar incidencias de '.Auth::user()->coordinator->comittee->name)

@section('title-icon', 'fas fa-clipboard-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <x-incidencelistcoordinator/>    
    <br>
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
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Recibida</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Estado</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($incidences as $incidence)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$incidence->id}}</td>
                                <td>
                                    <a href="{{route('coordinator.incidence.view',
                                                ['instance' => $instance, 'id' => $incidence->id])}}">
                                        {{$incidence->title}}
                                    </a>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$incidence->user->surname}}
                                    , {{$incidence->user->name}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($incidence->created_at)->diffForHumans() }} </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-incidencestatus :incidence="$incidence"/>
                                </td>

                                <td class="align-middle">
                                <x-incidenceoptionscoordinator :incidence="$incidence"/>
                                </td>

                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

            {{ $incidences->links() }}

        </div>

        
    </div>

@section('scripts')

    <script>

        $(document).ready(function () {
            countdown("{{\Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(Config::validate_incidences_timestamp(),false)}}");
        });

    </script>

@endsection

@endsection
