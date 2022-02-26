@extends('layouts.app')

@section('title', 'Actualizaciones')

@section('title-icon', 'fab fa-github')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">

                    @foreach($releases as $release)
                        <h3 class="mb-0"><a href="{{$release->html_url}}">{{$release->name}}</a>

                        </h3>

                        <span class="text-muted">
                            {{\Carbon\Carbon::createFromDate($release->published_at)->diffForHumans()}}
                        </span>

                        <br><br>

                        <p>{!!nl2br(e($release->body)) !!}</p>

                        <a class="btn btn-default btn-sm" href="{{$release->zipball_url}}">
                            <i class="fas fa-file-archive"></i>
                            Código fuente (zip)
                        </a>

                        <a class="btn btn-default btn-sm" href="{{$release->tarball_url}}">
                            <i class="fas fa-file-archive"></i>
                            Código fuente (tar.gz)
                        </a>

                        <br><hr>
                    @endforeach

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h3>Issues abiertas</h3>

                    @foreach($issues as $issue)

                        @foreach((array($issue->labels)) as $labels)
                            @foreach($labels as $l)
                                <span class="badge badge-pill

                                 @if($l->name == "bug")
                                    badge-danger
                                   @else
                                    badge-light
                                    @endif

                                 ">
                                    {{$l->name}}
                                </span>
                            @endforeach

                        @endforeach

                        <h6 class="mb-0"><a href="{{$issue->html_url}}">{{$issue->title}}</a></h6>

                            <span class="text-muted">
                            {{\Carbon\Carbon::createFromDate($issue->created_at)->diffForHumans()}}
                        </span>

                        <p>{!!nl2br(e($issue->body)) !!}</p>

                    @endforeach

                </div>

            </div>

        </div>

    </div>


@endsection
