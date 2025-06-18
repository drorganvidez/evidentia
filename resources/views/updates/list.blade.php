@extends('layouts.app')

@section('title', 'Actualizaciones')

@section('title-icon', 'fab fa-github')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-8">

            <div class="card">

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

<div class="card">
    <div class="card-body">

        <h3 class="mb-4">
            <i class="fas fa-exclamation-circle me-2 text-danger"></i> Issues abiertas
        </h3>

        @forelse($issues as $issue)
            <div class="mb-4 pb-3 border-bottom">

                {{-- Labels --}}
                <div class="mb-2">
                    @foreach($issue->labels as $label)
                        <span class="badge rounded-pill 
                            @if($label->name === 'bug') bg-danger 
                            @elseif($label->name === 'enhancement') bg-success 
                            @elseif($label->name === 'question') bg-warning text-dark 
                            @else bg-secondary 
                            @endif">
                            {{ $label->name }}
                        </span>
                    @endforeach
                </div>

                {{-- Título + enlace --}}
                <h6 class="mb-1">
                    <a href="{{ $issue->html_url }}" target="_blank" class="text-decoration-none">
                        {{ $issue->title }}
                    </a>
                </h6>

                {{-- Fecha --}}
                <small class="text-muted">
                    Creado {{ \Carbon\Carbon::parse($issue->created_at)->diffForHumans() }}
                </small>
            </div>

        @empty
            <p class="text-muted">No hay issues abiertas.</p>
        @endforelse

    </div>
</div>


        </div>

    </div>


@endsection
