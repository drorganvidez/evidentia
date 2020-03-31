@extends('layouts.app')

@section('title', 'Mis evidencias')

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
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
                                <td>{{$evidence->title}}</td>
                                <td>{{$evidence->hours}}</td>
                                <td>{{$evidence->comittee->comittee}}</td>
                                <td> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
                                <td>{{$evidence->status}}</td>

                                <td>

                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

            {{ $evidences->links() }}

        </div>
    </div>

@endsection
