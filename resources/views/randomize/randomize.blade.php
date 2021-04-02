@extends('layouts.app')

@section('title', 'Aleatorizar evidencias')

@section('title-icon', 'fas fa-random')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">

                    <form action="{{$route}}" method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-lg-6">

                                <p>
                                    Esta herramienta selecciona al azar una evidencia aceptada de cada alumno
                                    para su posterior evaluación.
                                </p>

                                <p>
                                    Es posible ejecutar varias veces esta opción. La evidencia seleccionada de cada
                                    alumno cada vez podría cambiar.
                                </p>

                            </div>

                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-6 col-sm-6 col-12">
                                <button type="submit" class="btn btn-primary btn-block">Aleatorizar evidencias</button>
                            </div>
                        </div>

                    </form>


                </div>

            </div>


        </div>
    </div>

@endsection
