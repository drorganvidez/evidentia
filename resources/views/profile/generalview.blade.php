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


            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img width="100" height="100" class="img-circle elevation-2"
                             src="{{route('avatar',['instance' => $instance, 'id' => $user->id])}}"
                             alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{!! $user->name!!} {!!  $user->surname!!}</h3>

                    <x-participation :user="$user"/>

                    <p class="text-muted text-center">
                        @foreach($user->roles as $rol)
                            <span class="badge badge-pill badge-secondary">{{$rol->slug}}</span>
                        @endforeach
                    </p>

                    <p style="text-justify: auto">{!! $user->biography !!}</p>


                </div>

            </div>


        </div>

        <div class="col-md-9">
            <div class="card">

                <div class="card-body">

                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Título</th>
                            <th>Horas</th>
                            <th>Comité</th>
                            <th>Creada</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($user->evidences as $evidence)
                            <tr>
                                <td><a  href="{{route('profiles.view.evidence',['instance' => $instance, 'id_user' => $user->id, 'id_evidence' => $evidence->id])}}">{{$evidence->title}}</a></td>
                                <td>{{$evidence->hours}}</td>
                                <td>
                                    <x-evidencecomittee :evidence="$evidence"/>
                                </td>
                                <td> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
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
