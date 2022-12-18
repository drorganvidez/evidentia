@extends('layouts.app')

@section('title', 'Mis tableros')

@section('title-icon', 'fas fa-id-badge')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')
    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">

                    <div class="table-responsive">

                        <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
                            <th>Título</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Creado</th>
                            <th>Herramientas</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($kanban as $kanbantable)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$kanbantable->id}}</td>
                                <td><a href="{{route('kanban.view',['instance' => $instance, 'id' => $kanbantable->id])}}">{{$kanbantable->title}}</a></td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-kanbantablecomittee :kanbantable="$kanbantable"/>
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"> {{ \Carbon\Carbon::parse($kanbantable->created_at)->diffForHumans() }} </td>
                                
                                <td class="align-middle">
                                    <x-kanbantableoptionsstudent :kanbantable="$kanbantable"/>
                                    <x-buttonconfirm :id="$kanbantable->id" route="kanban.remove_kanban" title="¿Seguro?" description="Esto borrará el tablero kanban actual
                                        y todas las tareas asociadas." type="REMOVE"/>
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