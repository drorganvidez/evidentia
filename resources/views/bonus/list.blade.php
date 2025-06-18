@extends('layouts.app')

@section('title', 'Gestionar bonos')

@section('title-icon', 'fas fa-puzzle-piece')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">
            @if (!\Carbon\Carbon::now()->gt(\Config::bonus_timestamp()))
                <div class="row mb-3">
                    <div class="col-lg-3 mt-1">
                        <a href="{{ route('secretary.bonus.create', []) }}" class="btn btn-primary btn-block" role="button"><i
                                class="fas fa-plus"></i> &nbsp;Crear nuevo bono de horas</a>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                            <tr>
                                <th scope="col">Razón</th>
                                <th scope="col">Horas</th>
                                @if (!\Carbon\Carbon::now()->gt(\Config::bonus_timestamp()))
                                    <th scope="col">Herramientas</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bonus as $bono)
                                <tr scope="row">
                                    <td>{{ $bono->reason }}</td>
                                    <td>{{ $bono->hours }}</td>
                                    @if (!\Carbon\Carbon::now()->gt(\Config::bonus_timestamp()))
                                        <td>
                                            <a class="btn btn-primary btn-sm"
                                                href="{{ route('secretary.bonus.edit', ['id' => $bono->id]) }}"
                                                role="button">
                                                <i class="far fa-edit"></i>
                                                <span class="d-none d-sm-none d-md-none d-lg-inline">Editar bono</span>
                                            </a>

                                            <x-buttonconfirm :id="$bono->id" route="secretary.bonus.remove"
                                                title="¿Seguro?"
                                                description="Las horas asociadas a los alumnos se borrarán."
                                                type="REMOVE" />

                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-6 ">

            <div class="card">
                <div class="card-body">

                    <div class="alert alert-info">
                        <h4 class="mb-2">
                            <i class="fas fa-stopwatch"></i> Fecha límite para registrar bonos
                        </h4>
                        <p class="mb-0">
                            {{ \Carbon\Carbon::parse(\Config::bonus_timestamp())->format('d/m/Y \a \l\a\s H:i') }}
                        </p>
                        <div class="countdown_container mt-2" style="display: none">
                            <p>
                                Quedan <b><span id="countdown"></span></b> para registrar bonos de horas.
                            </p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h5>Acerca del registro de bonos</h5>
                        <p class="text-justify">
                            Los bonos deben registrarse antes de la fecha límite establecida. Una vez pasado el plazo, no se
                            admitirán solicitudes ni modificaciones, salvo casos excepcionales y debidamente justificados.
                            <br><br>
                            Te recomendamos no esperar al último momento para evitar imprevistos y garantizar que tu
                            solicitud quede correctamente registrada.
                        </p>
                    </div>

                </div>
            </div>


        </div>


    </div>

@section('scripts')

    <script>
        $(document).ready(function() {
            countdown(
                "{{ \Carbon\Carbon::create(\Carbon\Carbon::now())->diffInSeconds(Config::bonus_timestamp(), false) }}"
                );
        });
    </script>

@endsection


@endsection
