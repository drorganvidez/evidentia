@extends('layouts.app')

@section('title', 'Gestionar listas')

@section('title-icon', 'far fa-list-alt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="row mb-3">
                <div class="col-lg-2 mt-1">
                    <a href="{{route('secretary.defaultlist.create',['instance' => $instance])}}" class="btn btn-primary btn-block" role="button"><i class="fas fa-plus"></i> &nbsp;Crear nueva lista</a>
                </div>
            </div>

            <div class="card">


                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Título de la lista</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Creada</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($defaultlists as $defaultlist)
                            <tr>
                                <td>{{$defaultlist->name}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{ \Carbon\Carbon::parse($defaultlist->created_at)->diffForHumans() }}</td>
                                <td>
                                    <a class="btn btn-primary btn-sm"
                                       href="{{route('secretary.defaultlist.edit',['instance' => $instance, 'id' => $defaultlist->id])}}"
                                       role="button">
                                        <i class="far fa-edit"></i>
                                        <span class="d-none d-sm-none d-md-none d-lg-inline">Editar lista</span>
                                    </a>

                                    <x-buttonconfirm :id="$defaultlist->id" route="secretary.defaultlist.remove" title="¿Seguro?" description="Esta acción es permanente." type="REMOVE" />

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
