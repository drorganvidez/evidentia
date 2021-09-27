@extends('layouts.app')

@section('title', 'Gestionar bonos')

@section('title-icon', 'fas fa-puzzle-piece')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">
            @if(!\Carbon\Carbon::now()->gt(\Config::bonus_timestamp()))
                <div class="row mb-3">
                    <div class="col-lg-3 mt-1">
                        <a href="{{route('secretary.bonus.create',['instance' => $instance])}}" class="btn btn-primary btn-block" role="button"><i class="fas fa-plus"></i> &nbsp;Crear nuevo bono de horas</a>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <div class="row">
        <div class="col-lg-8">



            <div class="card shadow-lg">

                <div class="card-body">
                    <table id="dataset" class="table table-hover table-responsive">
                            <thead>
                            <tr>
                                <th scope="col">Razón</th>
                                <th scope="col">Horas</th>
                                @if(!\Carbon\Carbon::now()->gt(\Config::bonus_timestamp()))
                                <th scope="col">Herramientas</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($bonus as $bono)
                                <tr scope="row">
                                    <td>{{$bono->reason}}</td>
                                    <td>{{$bono->hours}}</td>
                                    @if(!\Carbon\Carbon::now()->gt(\Config::bonus_timestamp()))
                                    <td>
                                        <a class="btn btn-primary btn-sm"
                                           href="{{route('secretary.bonus.edit',['instance' => $instance, 'id' => $bono->id])}}"
                                           role="button">
                                            <i class="far fa-edit"></i>
                                            <span class="d-none d-sm-none d-md-none d-lg-inline">Editar bono</span>
                                        </a>

                                        <x-buttonconfirm :id="$bono->id" route="secretary.bonus.remove" title="¿Seguro?" description="Las horas asociadas a los alumnos se borrarán." type="REMOVE" />

                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-lg">

                <div class="card-body">

                    <div class="callout callout-info">
                        <h4>

                            Fecha límite para el registro de bonos

                        </h4>

                        <h4>

                            <i class="fas fa-stopwatch"></i>

                            {{\Carbon\Carbon::parse(Config::bonus_timestamp())->format('d/m/Y')}}

                            a las

                            {{\Carbon\Carbon::parse(Config::bonus_timestamp())->format('H:i')}}

                        </h4>

                        <div class="countdown_container" style="display: none">

                            <p>

                                Quedan

                                <b>
                                    <span id="countdown"></span>
                                </b>

                                para registrar bonos de horas.

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>

@section('scripts')

    <script>

        $(document).ready(function(){
            countdown("{{\Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(Config::bonus_timestamp(),false)}}");
        });

    </script>

@endsection


@endsection
