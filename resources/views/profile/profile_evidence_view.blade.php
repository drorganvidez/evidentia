@extends('layouts.app')

@section('title', $user->surname . ', ' .$user->name . ': ' . $evidence->title)
@section('title-icon', 'nav-icon fas fa-user')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item"><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->surname}}, {{$user->name}}</a></li>
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

                    <div class="row">

                        <div class="col-lg-6">

                            <h5>
                                <x-evidencecomittee :evidence="$evidence"/>
                                <span class="badge badge-secondary">
                                                <i class="far fa-clock"></i> {{$evidence->hours}} horas
                                            </span>
                            </h5>

                            <h4>{{$evidence->title}}</h4>

                            <div class="post text-justify">
                                {!! $evidence->description !!}
                            </div>

                        </div>

                        <div class="col-lg-6">

                            <div class="text-muted">
                                <p class="text-sm">Última edición
                                    <b class="d-block">{{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }}</b>
                                </p>
                            </div>

                            <x-evidencestatus :evidence="$evidence"/>

                            <h5 class="text-muted mt-2">Archivos adjuntos</h5>
                            <div class="card-body table-responsive p-0">
                                <table class="table text-nowrap table-borderless">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Tamaño</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($evidence->proofs as $proof)

                                        <tr>
                                            <td>
                                                <a class="btn btn-primary btn-sm" href="{{route('file.download',['instance' => $instance, 'id' => $proof->file->id])}}">
                                                    <i class="fas fa-download"></i>
                                                    {{$proof->file->name}}
                                                </a>
                                            </td>
                                            <td>{{$proof->file->sizeForHuman()}}</td>

                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                            </div>


                        </div>

                    </div>


                </div>

            </div>


        </div>

    </div>


@endsection
