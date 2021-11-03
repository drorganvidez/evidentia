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

        <div class="col-md-4">

            <x-profile :user="$user"/>

        </div>

        <div class="col-md-8">

            <div class="card shadow-sm">

                <div class="card-body">

                    <div class="row">

                        <div class="col-lg-8">

                            <h5>
                                <x-evidencecomittee :evidence="$evidence"/>

                                <span class="badge badge-secondary">
                                    <i class="far fa-clock"></i> {{$evidence->hours}} horas
                                </span>

                                <span class="badge badge-secondary">
                                    @if($evidence->integrity())
                                        Integridad <i class="fas fa-check-circle"></i>
                                    @else
                                        <i  class="fas fa-times-circle"></i>
                                    @endif
                                </span>


                            </h5>



                            <h4>{{$evidence->title}}</h4>

                            <div class="post text-justify">
                                {!! $evidence->description !!}
                            </div>

                        </div>

                        <div class="col-lg-4">

                            <div class="text-muted">
                                <p class="text-sm">Última edición
                                    <b class="d-block">{{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }}</b>
                                </p>
                            </div>

                            <x-evidencestatus :evidence="$evidence"/>


                            <br>

                            @foreach($evidence->proofs as $proof)

                                <a style="margin-bottom: 10px" class="btn btn-primary btn-sm" href="{{route('proof.download',['instance' => $instance, 'id' => $proof->id])}}">
                                    <i class="fas fa-download"></i>
                                    {{$proof->file->name}} ({{$proof->file->sizeForHuman()}})
                                </a>

                            @endforeach


                        </div>

                    </div>


                </div>

            </div>



        </div>

    </div>

@endsection
