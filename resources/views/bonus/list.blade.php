@extends('layouts.app')

@section('title', 'Gestionar bonos')

@section('title-icon', 'fas fa-puzzle-piece')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::bonus_timestamp()"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="row mb-3">
                <div class="col-lg-3 mt-1">
                    <a href="{{route('secretary.bonus.create',['instance' => $instance])}}" class="btn btn-primary btn-block" role="button"><i class="fas fa-plus"></i> &nbsp;Crear nuevo bono de horas</a>
                </div>
            </div>

            <div class="card">


                <div class="card-body">
                    <table id="dataset" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Razón</th>
                                <th scope="col">Horas</th>
                                <th scope="col">Herramientas</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($bonus as $bono)
                                <tr scope="row">
                                    <td>{{$bono->reason}}</td>
                                    <td>{{$bono->hours}}</td>
                                    <td>
                                        <a class="btn btn-primary btn-sm"
                                           href="{{route('secretary.bonus.edit',['instance' => $instance, 'id' => $bono->id])}}"
                                           role="button">
                                            <i class="far fa-edit"></i>
                                            Editar bono</a>

                                        <x-buttonconfirm :id="$bono->id" route="secretary.bonus.remove" title="¿Seguro?" description="Las horas asociadas a los alumnos se borrarán." type="REMOVE" />

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
