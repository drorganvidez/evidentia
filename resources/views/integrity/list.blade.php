@extends('layouts.app')

@section('title', 'Comprobar integridad')

@section('title-icon', 'nav-icon fas fa-check-double')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-12">

            <div class="card shadow-lg">

                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#evidences" data-toggle="tab">Evidencias</a></li>
                        <li class="nav-item"><a class="nav-link" href="#proofs" data-toggle="tab">Pruebas</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">

                        <div class="active tab-pane" id="evidences">

                            <table id="dataset" class="table table-hover table-responsive">
                                <thead>
                                <tr>
                                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
                                    <th>Evidencia</th>
                                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Propietario</th>
                                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Subida</th>
                                    <th>Integridad</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($evidences as $evidence)
                                    <tr>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$evidence->id}}</td>
                                        <td><a  href="{{route('profiles.view.evidence',['instance' => $instance, 'id_user' => $evidence->user->id, 'id_evidence' => $evidence->id])}}">{{$evidence->title}}</a></td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $evidence->user->id])}}">{{$evidence->user->surname}}, {{$evidence->user->name}}</a></td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }} </td>
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
                                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Evidencia asociada</th>
                                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Propietario</th>
                                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Subida</th>
                                    <th>Integridad</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($proofs as $proof)
                                    <tr>
                                        <td>{{$proof->file->name}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$proof->evidence->title}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$proof->evidence->user->surname}}, {{$proof->evidence->user->name}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($proof->created_at)->diffForHumans() }} </td>
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
