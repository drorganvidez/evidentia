@extends('layouts.app')

@section('title', 'Evaluación')

@section('submenu')

    <x-submenus.evaluation-menu/>

@endsection

@section('content')

    <div class="row">
        <div class="col-12">

            <!-- Card -->
            <div class="card card-inactive">
                <div class="card-body text-center">

                    <!-- Title -->
                    <h1>
                        Modo moderación
                    </h1>

                    @if($no_evidences)

                        <p class="text-muted">
                            No hemos encontrado evidencias pendientes de moderar
                        </p>

                    @else

                        <p class="text-muted">
                            Con esta funcionalidad, todas las evidencias pendientes se pondrán en cola para ser evaluadas por orden estricto de llegada
                        </p>

                        <form action="{{route('coordinator.evidences.moderate_p', \Instantiation::instance())}}" method="post">

                            @csrf

                            <button type="submit" href="#!" class="btn btn-primary">
                                Moderar evidencias
                            </button>

                        </form>

                    @endif




                </div>
            </div>

        </div>
    </div>

@endsection
