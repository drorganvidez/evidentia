@extends('layouts.app')

@section('title', $user->surname . ', ' .$user->name)
@section('title-icon', 'nav-icon fas fa-user')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-3 col-sm-12">
            <x-infoevidencetotalhours :user="$user" />
        </div>

        <div class="col-lg-3 col-sm-12">
            <x-infomeetinghours :user="$user" />
        </div>

        <div class="col-lg-3 col-sm-12">
            <x-infoattendeeshours :user="$user" />
        </div>

        <div class="col-lg-3 col-sm-12">
            <x-infobonushours :user="$user" />
        </div>

    </div>

    <div class="row">

        <div class="col-lg-12">
            <x-status/>
        </div>

        <div class="col-md-3">

            <x-profile :user="$user"/>

        </div>

        <div class="col-md-9">
            <div class="card">

                <div class="card-body">

                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
                            <th>Título</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Creada</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($user->evidences as $evidence)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->id}}</td>
                                <td><a  href="{{route('profiles.view.evidence',['instance' => $instance, 'id_user' => $user->id, 'id_evidence' => $evidence->id])}}">{{$evidence->title}}</a></td>
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
