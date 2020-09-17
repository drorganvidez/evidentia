@extends('layouts.app')

@section('title', 'Gestionar evidencias')

@section('title-icon', 'nav-icon fas fa-clipboard-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-3 col-sm-12">
            <div class="info-box">
                <span class="info-box-icon bg-light elevation-1"><i class="fas fa-pencil-ruler"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">Evidencias en borrador</span>
                    <span class="info-box-number">
                  {{\App\Evidence::evidences_draft()->count()}}
                </span>
                </div>

            </div>
        </div>
        <div class="col-lg-3 col-sm-12">
            <div class="info-box">
                <span class="info-box-icon bg-light elevation-1"><i class="fas fa-clock"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">Evidencias pendientes</span>
                    <span class="info-box-number">
                  {{\App\Evidence::evidences_pending()->count()}}
                </span>
                </div>

            </div>
        </div>
        <div class="col-lg-3 col-sm-12">
            <div class="info-box">
                <span class="info-box-icon bg-light elevation-1"><i class="far fa-thumbs-up"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">Evidencias aceptadas</span>
                    <span class="info-box-number">
                  {{\App\Evidence::evidences_accepted()->count()}}
                </span>
                </div>

            </div>
        </div>
        <div class="col-lg-3 col-sm-12">
            <div class="info-box">
                <span class="info-box-icon bg-light elevation-1"><i class="far fa-thumbs-down"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">Evidencias rechazadas</span>
                    <span class="info-box-number">
                  {{\App\Evidence::evidences_rejected()->count()}}
                </span>
                </div>

            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
                            <th>Título</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Apellido del autor</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Nombre del autor</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Creada</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($evidences as $evidence)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->id}}</td>
                                <td><a  href="{{route('profiles.view.evidence',['instance' => $instance, 'id_user' => $evidence->user->id, 'id_evidence' => $evidence->id])}}">{{$evidence->title}}</a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $evidence->user->id])}}">{{$evidence->user->surname}}</a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $evidence->user->id])}}">{{$evidence->user->name}}</a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->hours}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-evidencecomittee :evidence="$evidence"/>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
                                <td>
                                    <x-evidencestatus :evidence="$evidence"/>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

@endsection
