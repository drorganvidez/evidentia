@extends('layouts.app')

@section('title', 'Configurar curso')

@section('title-icon', 'nav-icon fas fa-cogs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <form method="POST" action="{{ $route }}">
        @csrf

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <h5>Subida de nuevas evidencias</h5>
                        <p>Establece una fecha máximo de tope de subidas de nuevas evidencias de tus alumnos.</p>

                        <div class="form-row">
                            <x-input col="6" attr="upload_evidences_date" type="date" :value="\Carbon\Carbon::parse($configuration?->upload_evidences_timestamp)?->format(
                                'Y-m-d',
                            ) ?? ''"
                                label="Día" description="Indica el día límite." />

                            <x-input col="6" attr="upload_evidences_time" type="time" :value="\Carbon\Carbon::parse($configuration?->upload_evidences_timestamp)?->format(
                                'H:i',
                            ) ?? ''"
                                label="Hora" description="Indica la hora límite." />
                        </div>

                        <hr>

                        <h5>Validación de evidencias</h5>
                        <p>Establece una fecha máximo de tope de validación de evidencias por parte de los coordinadores de
                            cada comité.</p>

                        <div class="form-row">
                            <x-input col="6" attr="validate_evidences_date" type="date" :value="\Carbon\Carbon::parse($configuration?->validate_evidences_timestamp)?->format(
                                'Y-m-d',
                            ) ?? ''"
                                label="Día" description="Indica el día límite." />

                            <x-input col="6" attr="validate_evidences_time" type="time" :value="\Carbon\Carbon::parse($configuration?->validate_evidences_timestamp)?->format(
                                'H:i',
                            ) ?? ''"
                                label="Hora" description="Indica la hora límite." />
                        </div>

                        <hr>

                        <h5>Registro de reuniones</h5>
                        <p>Establece una fecha máximo de tope de registro de reuniones por parte de los secretarios de cada
                            comité.</p>

                        <div class="form-row">
                            <x-input col="6" attr="meetings_date" type="date" :value="\Carbon\Carbon::parse($configuration?->meetings_timestamp)?->format('Y-m-d') ??
                                ''" label="Día"
                                description="Indica el día límite." />

                            <x-input col="6" attr="meetings_time" type="time" :value="\Carbon\Carbon::parse($configuration?->meetings_timestamp)?->format('H:i') ?? ''" label="Hora"
                                description="Indica la hora límite." />
                        </div>

                        <hr>

                        <h5>Registro de bonos</h5>
                        <p>Establece una fecha máximo de tope de registro de bonos de horas por parte de los secretarios de
                            cada comité.</p>

                        <div class="form-row">
                            <x-input col="6" attr="bonus_date" type="date" :value="\Carbon\Carbon::parse($configuration?->bonus_timestamp)?->format('Y-m-d') ?? ''" label="Día"
                                description="Indica el día límite." />

                            <x-input col="6" attr="bonus_time" type="time" :value="\Carbon\Carbon::parse($configuration?->bonus_timestamp)?->format('H:i') ?? ''" label="Hora"
                                description="Indica la hora límite." />
                        </div>

                        <hr>

                        <h5>Registro de eventos y asistencias</h5>
                        <p>Establece una fecha máximo de tope de registro de eventos y asistencia a los mismos por parte de
                            los coordinadores de registro.</p>

                        <div class="form-row">
                            <x-input col="6" attr="attendees_date" type="date" :value="\Carbon\Carbon::parse($configuration?->attendee_timestamp)?->format('Y-m-d') ??
                                ''" label="Día"
                                description="Indica el día límite." />

                            <x-input col="6" attr="attendees_time" type="time" :value="\Carbon\Carbon::parse($configuration?->attendee_timestamp)?->format('H:i') ?? ''" label="Hora"
                                description="Indica la hora límite." />
                        </div>

                        <div class="form-row mt-3">
                            <div class="form-group col-md-6">
                                <button type="submit" class="btn btn-primary">Guardar configuración</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </form>

@endsection
