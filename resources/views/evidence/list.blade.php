@extends('layouts.app')

@section('title', 'Mis evidencias')

@section('title-icon', 'fas fa-id-badge')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-3 col-sm-12">
            <x-infoevidencetotalhours :user="Auth::user()" />
        </div>
        <div class="col-lg-3 col-sm-12">
            <x-infoevidencetotalcountaccepted :user="Auth::user()" />
        </div>
        <div class="col-lg-3 col-sm-12">
            <x-infoevidencetotalcountpending :user="Auth::user()" />
        </div>
        <div class="col-lg-3 col-sm-12">
            <x-infoevidencetotalcountrejected :user="Auth::user()" />
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
                            <th>Título</th>
                            <th>Horas</th>
                            <th>Comité</th>
                            <th>Creada</th>
                            <th>Estado</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($evidences as $evidence)
                            <tr>
                                <td><a  href="{{route('evidence.view',['instance' => $instance, 'id' => $evidence->id])}}">{{$evidence->title}}</a></td>
                                <td>{{$evidence->hours}}</td>
                                <td>
                                    <x-evidencecomittee :evidence="$evidence"/>
                                </td>
                                <td> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
                                <td>
                                    <x-evidencestatus :evidence="$evidence"/>
                                </td>

                                <td>
                                    <x-evidenceoptionsstudent :evidence="$evidence"/>
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
