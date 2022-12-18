@extends('layouts.app')

@section('title', 'Gestionar archivos de alumno')

@section('title-icon', 'nav-icon fas fa-folder-gear')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8" id="user-data">
            <h3>Alumno: <a href="{{route('profiles.view',['instance'=> $instance, 'id' => $user->id])}}">{{$user->surname}}, {{$user->name}}</a></h3>
        </div>
        <div class="col-lg-4">
            <h2>Cuota de espacio ocupada: {{ $storage_used }}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table id="dataset" class="table">
                <caption>Evidencias con archivos no verificados</caption>
                <thead>
                    <tr>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID Evidencia</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Título</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID archivo</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Nombre de archivo</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Tipo</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Tamaño</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Descargar</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evidences as $evidence)
                        @foreach ($evidence->proofs as $proof)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$evidence->id}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$evidence->title}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->file_id}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->file->name}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->file->type}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->file->sizeForHuman()}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <a style="margin-bottom: 10px" class="btn btn-default btn-sm" href="{{route('proof.download',['instance' => $instance, 'id' => $proof->id])}}">
                                        <i class="fas fa-download"></i>
                                        Descargar
                                    </a>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <a style="margin-bottom: 10px" class="btn btn-default btn-sm" href="{{route('lecture.proof.verify', ['instance' => $instance,'user_id'=> $user->id, 'evidence_id' => $evidence->id, 'proof_id' => $proof->id])}}">
                                        <i class="fas fa-check"></i>
                                        Verificar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <h3>Evidencias verificadas</h3>
    </div>
    <div class="row">
        <div class="col-lg-12 mt-5">
            <table class="table" id="dataset_verified">
                <caption>Evidencias con archivos verificados</caption>
                <thead>
                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID Evidencia</th>
                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Título</th>
                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID archivo</th>
                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Nombre de archivo</th>
                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Tipo</th>
                    <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Tamaño</th>
                </thead>
                <tbody>
                    @foreach ($evidences as $evidence)
                        @foreach ($evidence->verified_proofs as $proof)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$evidence->id}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$evidence->title}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->file_id}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->name}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->type}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$proof->size}}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection