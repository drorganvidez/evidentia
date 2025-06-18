@extends('layouts.app')

@section('title', 'Dashboard')
@section('title-icon', 'fas fa-tachometer-alt')

@section('content')

    <div class="row">

        <div class="col-lg-5 col-md-12 col-sm-12 col-12">

            <div class="row">
                <div class="col-md-12">

                    <x-profile :user="Auth::user()"></x-profile>

                </div>
            </div>

        </div>

        <div class="col-lg-7 col-md-12 col-sm-12 col-12">

            <div class="row">

                <div class="col-lg-12">

                    <div class="card card-widget widget-user shadow-sm">
                        <div class="widget-user-header position-relative"
                            style="background: url({{ asset('dist/img/actas.png') }}) center center / cover no-repeat; min-height: 130px;">

                            {{-- Overlay para mejor contraste --}}
                            <div style="position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); z-index: 0;"></div>

                            <h5 class="widget-user-desc text-left text-white position-relative mb-0 pt-4 px-3"
                                style="font-weight: 600; font-size: 1.3rem; z-index: 1;">
                                <i class="fas fa-child mr-2"></i>
                                <i class="fas fa-scroll mr-3"></i>
                                Convocatorias y actas de reunión
                            </h5>
                        </div>

                        <div class="card-footer" style="padding-top: 10px;">
                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <div id="accordion" role="tablist" aria-multiselectable="true">
                                        @foreach (\App\Models\Committee::all() as $committee)
                                            <div class="card card-light mb-2">
                                                <div class="card-header" role="tab" id="heading-{{ $committee->id }}">
                                                    <h4 class="card-title mb-0">
                                                        <a class="d-block w-100 collapsed" data-toggle="collapse"
                                                            href="#collapse-{{ $committee->id }}" aria-expanded="false"
                                                            aria-controls="collapse-{{ $committee->id }}">
                                                            Comité de {{ $committee->name }}
                                                        </a>
                                                    </h4>
                                                </div>

                                                <div id="collapse-{{ $committee->id }}" class="collapse" role="tabpanel"
                                                    aria-labelledby="heading-{{ $committee->id }}" data-parent="#accordion">
                                                    <div class="card-body">
                                                        <h6><b>Convocatorias</b></h6>
                                                        <ul>
                                                            @foreach ($committee->get_all_meeting_requests() as $meeting_request)
                                                                <li>
                                                                    <a
                                                                        href="{{ route('download.request', ['id' => $meeting_request->id]) }}">
                                                                        {{ $meeting_request->title }}
                                                                    </a>
                                                                    ({{ \Carbon\Carbon::parse($meeting_request->datetime)->format('d/m/Y') }})
                                                                </li>
                                                            @endforeach
                                                        </ul>

                                                        <h6><b>Actas</b></h6>
                                                        <ul>
                                                            @foreach ($committee->get_all_meeting_minutes() as $meeting_minutes)
                                                                <li>
                                                                    <a
                                                                        href="{{ route('download.minutes', ['id' => $meeting_minutes->id]) }}">
                                                                        {{ $meeting_minutes->meeting->title }}
                                                                    </a>
                                                                    ({{ \Carbon\Carbon::parse($meeting_minutes->meeting->datetime)->format('d/m/Y') }})
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="row">

        <div class="col-lg-4 col-md-12 col-sm-12 col-12">

            <div class="card card-widget widget-user shadow-sm">
                <div class="widget-user-header position-relative"
                    style="background: url({{ asset('dist/img/eventos.png') }}) center center / cover no-repeat; min-height: 130px;">

                    {{-- Overlay negro semitransparente --}}
                    <div style="position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); z-index: 0;"></div>

                    <h5 class="widget-user-desc text-left text-white position-relative mb-0 pt-4 px-3"
                        style="font-weight: 600; font-size: 1.3rem; z-index: 1;">
                        <i class="fas fa-calendar-day mr-2"></i>
                        Eventos programados
                    </h5>



                </div>

                <div class="card-footer" style="padding-top: 10px;">
                    <div class="row">
                        <div class="col-lg-12 mt-3">
                            @forelse(\App\Models\Event::all() as $event)
                                <div class="callout callout-info">
                                    <h5>{{ $event->name }}</h5>
                                    <a target="_blank" href="{{ $event->url }}">{{ $event->url }}</a>
                                    <p>{{ $event->description }}</p>
                                    <b>
                                        Programado para el
                                        {{ \Carbon\Carbon::parse($event->start_datetime)->translatedFormat('j \d\e F \d\e Y') }}
                                        a las {{ \Carbon\Carbon::parse($event->start_datetime)->format('H:i') }}
                                    </b>
                                </div>
                            @empty
                                <div class="callout callout-warning">
                                    <h5>Ups...</h5>
                                    <p>Parece que el coordinador de registro aún no ha creado/cargado los eventos en
                                        Eventbrite.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <div class="col-lg-4 col-md-12 col-sm-12 col-12">
            <div class="card card-widget widget-user shadow-sm">
                <div class="widget-user-header position-relative"
                    style="background: url({{ asset('dist/img/proxima_reunion.png') }}) center center / cover no-repeat; min-height: 130px;">

                    {{-- Overlay negro semitransparente --}}
                    <div style="position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); z-index: 0;"></div>

                    <h5 class="widget-user-desc text-left text-white position-relative mb-0 pt-4 px-3"
                        style="font-weight: 600; font-size: 1.3rem; z-index: 1;">
                        <i class="far fa-handshake mr-2"></i>
                        Próximas reuniones
                    </h5>
                </div>

                <div class="card-footer pt-3">
                    <div class="row">
                        <div class="col-lg-12 mt-3">
                            <div id="accordion" role="tablist" aria-multiselectable="true">
                                @forelse(\App\Models\MeetingRequest::nextMeetingRequests() as $meeting)
                                    <div class="card card-light mb-2">
                                        <div class="card-header" role="tab" id="heading-{{ $meeting->id }}">
                                            <h4 class="card-title mb-0">
                                                <a class="d-block w-100 collapsed" data-toggle="collapse"
                                                    href="#collapse-{{ $meeting->id }}" aria-expanded="false"
                                                    aria-controls="collapse-{{ $meeting->id }}">
                                                    {{ $meeting->title }}
                                                    ({{ \Carbon\Carbon::parse($meeting->datetime)->day }} de
                                                    {{ \Carbon\Carbon::parse($meeting->datetime)->monthName }})
                                                </a>
                                            </h4>
                                        </div>

                                        <div id="collapse-{{ $meeting->id }}" class="collapse" role="tabpanel"
                                            aria-labelledby="heading-{{ $meeting->id }}" data-parent="#accordion">
                                            <div class="card-body">
                                                <p>
                                                    Programado para el
                                                    {{ \Carbon\Carbon::parse($meeting->datetime)->day }} de
                                                    {{ \Carbon\Carbon::parse($meeting->datetime)->monthName }} de
                                                    {{ \Carbon\Carbon::parse($meeting->datetime)->year }} a las
                                                    {{ \Carbon\Carbon::parse($meeting->datetime)->format('H:i') }}
                                                </p>
                                                <p>
                                                    Organizada por el comité de <b>{{ $meeting->committee->name }}</b> en
                                                    {{ $meeting->place }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="callout callout-warning">
                                        <h5>Vaya...</h5>
                                        <p>No se ha acordado ninguna reunión en los próximos días.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-4 col-md-12 col-sm-12 col-12">

            @php
                $config = \App\Models\Configuration::first();
            @endphp

            <div class="card card-widget widget-user shadow-sm">
                <div class="widget-user-header position-relative"
                    style="background: url({{ asset('dist/img/deadline.png') }}) center center / cover no-repeat; min-height: 130px;">

                    {{-- Overlay negro semitransparente --}}
                    <div style="position: absolute; inset: 0; background-color: rgba(0,0,0,0.6); z-index: 0;"></div>

                    <h5 class="widget-user-desc text-left text-white position-relative mb-0 pt-4 px-3"
                        style="font-weight: 600; font-size: 1.3rem; z-index: 1;">
                        <i class="fas fa-stopwatch mr-2"></i>
                        Fechas límite
                    </h5>
                </div>

                <div class="card-footer pt-2 pb-2">
                    @if ($config)
                        @php
                            $dates = [
                                'Subida de evidencias' => $config->upload_evidences_timestamp,
                                'Validación de evidencias' => $config->validate_evidences_timestamp,
                                'Registro de reuniones' => $config->meetings_timestamp,
                                'Registro de bonos' => $config->bonus_timestamp,
                                'Importación de eventos y asistencias' => $config->attendee_timestamp,
                            ];
                        @endphp

                        <ul class="list-group list-group-flush small">
                            @foreach ($dates as $label => $datetime)
                                @if ($datetime)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-1 py-1">
                                        <span>{{ $label }}</span>
                                        <span>
                                            {{ \Carbon\Carbon::parse($datetime)->format('d/m/Y') }}
                                            <small class="text-muted">a las
                                                {{ \Carbon\Carbon::parse($datetime)->format('H:i') }}</small>
                                        </span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <div class="callout callout-warning mb-0 p-2 text-center small">
                            <strong>Configuración no disponible:</strong> No se han configurado las fechas límite.
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>



@endsection
