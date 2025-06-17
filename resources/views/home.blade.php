@extends('layouts.app')

@section('title', 'Dashboard')
@section('title-icon', 'fas fa-tachometer-alt')

@section('content')

    <div class="row">

        <div class="col-md-4">

            <div class="row">
                <div class="col-md-12">

                    <x-profile :user="Auth::user()"></x-profile>

                </div>
            </div>

        </div>

        <div class="col-md-8">

            <div class="row">

                <div class="col-lg-6">

                    <div class="row">

                        <div class="col-lg-12">

                            <div class="card card-widget widget-user shadow-sm">

                                <div class="widget-user-header text-white"
                                     style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px">
                                        <i class="fas fa-child"></i> <i class="fas fa-scroll"></i>
                                        &nbsp;&nbsp;
                                        Convocatorias y actas de reunión
                                    </h5>

                                </div>

                                <div class="card-footer" style="padding-top: 10px">
                                    <div class="row">

                                        <div class="col-lg-12 mt-3">
                                            <span class="description-text"
                                                  style="font-size: 20px; text-align: left;"></span>

                                            <div id="accordion">

                                                @foreach(\App\Models\Committee::all() as $committee)
                                                    <div class="card card-light">
                                                        <div class="card-header">
                                                            <h4 class="card-title w-100">
                                                                <a class="d-block w-100 collapsed"
                                                                   data-toggle="collapse" href="#{{$committee->name}}"
                                                                   aria-expanded="false">
                                                                    Comité de {{$committee->name}}
                                                                </a>
                                                            </h4>
                                                        </div>
                                                        <div id="{{$committee->name}}" class="collapse"
                                                             data-parent="#accordion" style="">
                                                            <div class="card-body">

                                                                <h6><b>Convocatorias</b></h6>

                                                                <ul>
                                                                    @foreach($committee->get_all_meeting_requests() as $meeting_request)

                                                                        <li>
                                                                            <a href="{{route('download.request',['instance' => \Instantiation::instance(), 'id' => $meeting_request->id])}}">{{$meeting_request->title}}</a>

                                                                            ({{\Carbon\Carbon::parse($meeting_request->datetime)->format('d/m/Y')}}
                                                                            )
                                                                        </li>

                                                                    @endforeach
                                                                </ul>

                                                                <h6><b>Actas</b></h6>

                                                                <ul>
                                                                    @foreach($committee->get_all_meeting_minutes() as $meeting_minutes)

                                                                        <li>
                                                                            <a href="{{route('download.minutes',['instance' => \Instantiation::instance(), 'id' => $meeting_minutes->id])}}">{{$meeting_minutes->meeting->title}}</a>

                                                                            ({{\Carbon\Carbon::parse($meeting_minutes->meeting->datetime)->format('d/m/Y')}}
                                                                            )
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

                <div class="col-lg-6">

                    <div class="row">

                        <div class="col-lg-12">

                            <div class="card card-widget widget-user shadow-sm">

                                <div class="widget-user-header text-white"
                                     style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

                                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px">
                                        <i class="fas fa-calendar-day"></i>
                                        &nbsp;&nbsp;
                                        Eventos programados
                                    </h5>

                                </div>

                                <div class="card-footer" style="padding-top: 10px">
                                    <div class="row">

                                        <div class="col-lg-12 mt-3">
                                            <span class="description-text"
                                                  style="font-size: 20px; text-align: left;"></span>

                                            @forelse(\App\Models\Event::all() as $event)

                                                <div class="callout callout-info">
                                                    <h5>{!! $event->name !!}</h5>
                                                    <a target="_blank" href="{{$event->url}}">{{$event->url}}</a>

                                                    <p>{!! $event->description !!}</p>

                                                    <b>Programado para el
                                                        {{\Carbon\Carbon::parse($event->start_datetime)->day}}
                                                        {{\Carbon\Carbon::parse($event->start_datetime)->monthName}}
                                                        de
                                                        {{\Carbon\Carbon::parse($event->start_datetime)->year}}
                                                        a las
                                                        {{\Carbon\Carbon::parse($event->start_datetime)->format('H:i')}}
                                                    </b>
                                                </div>

                                            @empty

                                                <div class="callout callout-warning">
                                                    <h5>Ups...</h5>

                                                    <p>Parece que el coordinador de registro aún no ha creado/cargado
                                                        los eventos
                                                        en Eventbrite.</p>
                                                </div>

                                            @endforelse

                                            <ul>

                                                @foreach(\App\Models\Event::all() as $event)
                                                    <li>
                                                        {{$event->name}}
                                                    </li>
                                                    <li>
                                                        {{$event}}
                                                    </li>
                                                @endforeach

                                            </ul>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="col-lg-12">
                            <div class="card card-widget widget-user shadow-sm">

                                <div class="widget-user-header text-white"
                                     style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">
                                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px">
                                        <i class="far fa-handshake"></i>
                                        &nbsp;&nbsp;
                                        Próximas reuniones
                                    </h5>
                                </div>


                                <div class="card-footer" style="padding-top: 10px">
                                    <div class="row">
                                        <div class="col-lg-12 mt-3">
                                            <div id="accordion">
                                                @forelse(\App\Models\MeetingRequest::nextMeetingRequests() as $meeting)
                                                    <div class="card card-light">
                                                        <div class="card-header">
                                                            <h4 class="card-title w-100">
                                                                <a class="d-block w-100 collapsed"
                                                                   data-toggle="collapse" href="#id{{$meeting->id}}"
                                                                   aria-expanded="false">
                                                                    {{$meeting->title}} ({{\Carbon\Carbon::parse($meeting->datetime)->day}}
                                                                                            de
                                                                                         {{\Carbon\Carbon::parse($meeting->datetime)->monthName}})
                                                                </a>
                                                            </h4>
                                                        </div>
                                                        <div id="id{{$meeting->id}}" class="collapse"
                                                             data-parent="#accordion" style="">
                                                            <div class="card-body">
                                                                <p>Programado para el
                                                                    {{\Carbon\Carbon::parse($meeting->datetime)->day}}
                                                                    de
                                                                    {{\Carbon\Carbon::parse($meeting->datetime)->monthName}}
                                                                    de
                                                                    {{\Carbon\Carbon::parse($meeting->datetime)->year}}
                                                                    a las
                                                                    {{\Carbon\Carbon::parse($meeting->datetime)->format('H:i')}}
                                                                </p>
                                                                <p>
                                                                    Organizada por el comité
                                                                    de <b>{{$meeting->comittee->name}}</b>
                                                                    en {{$meeting->place}}
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


                        <div class="col-lg-12">
                            <div class="card card-widget widget-user shadow-sm">

                                <div class="widget-user-header text-white"
                                     style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">
                                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px">
                                        <i class="fas fa-stopwatch"></i>
                                        &nbsp;&nbsp;
                                        Fechas límite
                                    </h5>
                                </div>

                                <div class="card-footer" style="padding-top: 10px">

                                    <div class="callout callout-info mt-3">
                                        <h5>Subida de evidencias</h5>

                                        <p>
                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->upload_evidences_timestamp)->format('d/m/Y')}}

                                            a las

                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->upload_evidences_timestamp)->format('H:i')}}
                                        </p>
                                    </div>

                                    <div class="callout callout-info mt-3">
                                        <h5>Validación de evidencias</h5>

                                        <p>
                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->validate_evidences_timestamp)->format('d/m/Y')}}

                                            a las

                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->validate_evidences_timestamp)->format('H:i')}}
                                        </p>
                                    </div>

                                    <div class="callout callout-info mt-3">
                                        <h5>Registro de reuniones</h5>

                                        <p>
                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->meetings_timestamp)->format('d/m/Y')}}

                                            a las

                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->meetings_timestamp)->format('H:i')}}
                                        </p>
                                    </div>

                                    <div class="callout callout-info mt-3">
                                        <h5>Registro de bonos</h5>

                                        <p>
                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->bonus_timestamp)->format('d/m/Y')}}

                                            a las

                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->bonus_timestamp)->format('H:i')}}
                                        </p>
                                    </div>

                                    <div class="callout callout-info mt-3">
                                        <h5>Importación de eventos y asistencias</h5>

                                        <p>
                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->attendee_timestamp)->format('d/m/Y')}}

                                            a las

                                            {{\Carbon\Carbon::parse(\App\Models\Configuration::first()->attendee_timestamp)->format('H:i')}}
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>


                    </div>


                </div>

            </div>

        </div>


    </div>


@endsection
