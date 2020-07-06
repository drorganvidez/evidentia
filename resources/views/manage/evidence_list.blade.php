@extends('layouts.app')

@section('title', 'Gestionar evidencias')

@section('title-icon', 'nav-icon fas fa-clipboard-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Título</th>
                            <th>Apellido del autor</th>
                            <th>Nombre del autor</th>
                            <th>Horas</th>
                            <th>Comité</th>
                            <th>Creada</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($evidences as $evidence)
                            <tr>
                                <td><a  href="{{route('profiles.view.evidence',['instance' => $instance, 'id_user' => $evidence->user->id, 'id_evidence' => $evidence->id])}}">{{$evidence->title}}</a></td>
                                <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $evidence->user->id])}}">{{$evidence->user->surname}}</a></td>
                                <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $evidence->user->id])}}">{{$evidence->user->name}}</a></td>
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
