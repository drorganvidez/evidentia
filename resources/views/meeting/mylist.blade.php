@extends('layouts.app')

@section('title', 'Mis reuniones')

@section('title-icon', 'nav-icon fas fa-cocktail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-3">

            <x-infomeetingcount :user="Auth::user()" />


        </div>

        <div class="col-lg-3">

            <x-infomeetinghours :user="Auth::user()" />

        </div>

    </div>

    <div class="row">

        <div class="col-lg-12">

            <div class="card">

                <div class="card-body table-responsive p-0">

                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap m-0">
                            <thead>
                            <tr>
                                <th scope="col">Reunión</th>
                                <th scope="col">Lugar</th>
                                <th scope="col">Horas</th>
                                <th scope="col">Realizada</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($meetings as $meeting)
                                <tr scope="row">
                                    <td>{{$meeting->title}}</td>
                                    <td>{{$meeting->place}}</td>
                                    <td>{{$meeting->hours}}</td>
                                    <td>{{ \Carbon\Carbon::parse($meeting->datetime)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>

            </div>

            {{ $meetings->links() }}


        </div>


    </div>

@endsection
