@extends('layouts.app')

@section('title', 'Crear acta')

@section('title-icon', 'fas fa-scroll')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage')}}">Gestionar reuniones</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting/>

        <div class="col-md-8">

            <div class="card">

                <div class="card-body">

                    <div class="bs-stepper linear">

                    <div class="bs-stepper-header" role="tablist">

                        <div class="step active" data-target="#step_1">
                            <button type="button" class="step-trigger" role="tab" aria-controls="logins-part" id="logins-part-trigger" aria-selected="true">
                                <span class="bs-stepper-circle">1</span>
                                <span class="bs-stepper-label">Asociar convocatoria</span>
                            </button>
                        </div>

                        <div class="line"></div>

                        <div class="step">
                            <button type="button" class="step-trigger" role="tab" aria-controls="information-part" id="information-part-trigger" aria-selected="false" disabled="disabled">
                                <span class="bs-stepper-circle">2</span>
                                <span class="bs-stepper-label">Asociar asistencias</span>
                            </button>
                        </div>

                        <div class="line"></div>

                        <div class="step">
                            <button type="button" class="step-trigger" role="tab" aria-controls="information-part" id="information-part-trigger" aria-selected="false" disabled="disabled">
                                <span class="bs-stepper-circle">3</span>
                                <span class="bs-stepper-label">Redactar acta</span>
                            </button>
                        </div>

                    </div>

                    <div class="bs-stepper-content">
                        <!-- your steps content here -->

                        <div id="step_1" class="content active" role="tabpanel">

                            <form method="POST" action="{{route('secretary.meeting.manage.minutes.create.step1_p')}}">
                                @csrf

                                <div class="form-row">

                                    <div class="form-group col-md-6">
                                        <label for="meeting_request">Asociar convocatoria</label>
                                        <select id="meeting_request" class="selectpicker form-control @error('meeting_request') is-invalid @enderror" name="meeting_request" value="{{ old('meeting_request') }}" autofocus>

                                            <option value="">
                                                No asociar ninguna convocatoria
                                            </option>

                                            @foreach($meeting_requests as $meeting_request)
                                                <option {{$meeting_request->id == old('$meeting_request') ? 'selected' : ''}} value="{{$meeting_request->id}}">
                                                    {!! $meeting_request->title !!}
                                                </option>
                                            @endforeach
                                        </select>

                                        <small class="form-text text-muted">
                                            Asocia una convocatoria al acta.
                                        </small>

                                        @error('meeting_request')
                                            <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                </div>

                                <div class="form-row">

                                    <div class="form-group col-md-">
                                        <button type="submit"  class="btn btn-primary">
                                            Siguiente&nbsp;&nbsp;<i class="fas fa-chevron-circle-right"></i>
                                        </button>
                                    </div>

                                </div>

                            </form>

                        </div>
                    </div>
                </div>

                </div>

            </div>


        </div>
    </div>



@endsection
