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
                                {{$evidence->proof}}
                            </td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"></td>
                        </tr>
                    @endforeach
                @endforeach


            </table>
        </div>
    </div>
@endsection