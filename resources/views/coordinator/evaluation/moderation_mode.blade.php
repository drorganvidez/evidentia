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

                    <!-- Image -->
                    <img src="assets/img/illustrations/scale.svg" alt="..." class="img-fluid" style="max-width: 182px;">

                    <!-- Title -->
                    <h1>
                        Modo moderación
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-muted">
                        Con esta funcionalidad, todas las evidencias pendientes se pondrán en cola para ser evaluadas por orden estricto de llegada
                    </p>

                    <!-- Button -->
                    <a href="#!" class="btn btn-primary">
                        Create Report
                    </a>

                </div>
            </div>

        </div>
    </div>

@endsection
