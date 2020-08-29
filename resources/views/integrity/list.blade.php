@extends('layouts.app')

@section('title', 'Comprobar integridad')

@section('title-icon', 'nav-icon fas fa-check-double')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">
            <x-status/>
        </div>

        <div class="col-md-12">

            <div class="card">

                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#evidences" data-toggle="tab">Integridad de evidencias</a></li>
                        <li class="nav-item"><a class="nav-link" href="#proofs" data-toggle="tab">Integridad de pruebas</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">

                        <div class="active tab-pane" id="evidences">

                            <table id="dataset" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Evidencia</th>
                                    <th>Propietario</th>
                                    <th>Subida</th>
                                    <th>Integridad</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($evidences as $evidence)
                                    <tr>
                                        <td>{{$evidence->title}}</td>
                                        <td>{{$evidence->user->surname}}, {{$evidence->user->name}}</td>
                                        <td> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
                                        <td class="text-center">
                                            @if($evidence->integrity())
                                                <i style="color: green" class="fas fa-check-circle"></i>
                                            @else
                                                <i style="color: red" class="fas fa-times-circle"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>

                        </div>

                        <div class="tab-pane" id="proofs">

                            <table id="dataset2" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Prueba</th>
                                    <th>Evidencia asociada</th>
                                    <th>Propietario</th>
                                    <th>Subida</th>
                                    <th>Integridad</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($proofs as $proof)
                                    <tr>
                                        <td>{{$proof->file->name}}</td>
                                        <td>{{$proof->evidence->title}}</td>
                                        <td>{{$proof->evidence->user->surname}}, {{$proof->evidence->user->name}}</td>
                                        <td> {{ \Carbon\Carbon::parse($proof->created_at)->diffForHumans() }} </td>
                                        <td class="text-center">
                                            @if($proof->integrity())
                                                <i style="color: green" class="fas fa-check-circle"></i>
                                            @else
                                                <i style="color: red" class="fas fa-times-circle"></i>
                                            @endif
                                        </td>
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


@endsection
