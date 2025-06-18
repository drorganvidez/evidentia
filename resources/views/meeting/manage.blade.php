@extends('layouts.app')

@section('title', 'Gestionar reuniones')

@section('title-icon', 'far fa-handshake')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting />

        <div class="col-md-8">

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i> Pasos para gestionar correctamente las reuniones
                    </h5>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <h6 class="fw-bold"><i class="fas fa-calendar-plus me-2 text-info"></i> 1. Crear convocatoria de
                            reunión</h6>
                        <p class="text-muted mb-0">
                            Define los puntos del orden del día que se tratarán durante la reunión.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold"><i class="fas fa-pen-square me-2 text-info"></i> 2. Crear hoja de firmas</h6>
                        <p class="text-muted mb-0">
                            Los asistentes firmarán su asistencia mediante una URL personalizada accediendo con su usuario y
                            contraseña.
                        </p>
                    </div>

                    <div>
                        <h6 class="fw-bold"><i class="fas fa-file-alt me-2 text-info"></i> 3. Crear acta de reunión</h6>
                        <p class="text-muted mb-0">
                            El acta se genera automáticamente con los puntos del día y las firmas recogidas.
                        </p>
                    </div>

                </div>
            </div>



        </div>
    </div>


@endsection
