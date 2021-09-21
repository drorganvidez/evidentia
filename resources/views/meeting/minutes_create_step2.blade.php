@extends('layouts.app')

@section('title', 'Crear acta')

@section('title-icon', 'fas fa-scroll')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage',\Instantiation::instance())}}">Gestionar reuniones</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting/>

        <div class="col-md-9">

            <div class="card shadow-sm">

                <div class="bs-stepper linear">

                    <div class="bs-stepper-header" role="tablist">

                        <div class="step" data-target="#step_1">
                            <button type="button" class="step-trigger" role="tab" aria-controls="logins-part" id="logins-part-trigger" aria-selected="true" disabled="disabled">
                                <span class="bs-stepper-circle" style="background-color: #1aa179">1</span>
                                <span class="bs-stepper-label">Asociar convocatoria</span>
                            </button>
                        </div>

                        <div class="line"></div>

                        <div class="step active">
                            <button type="button" class="step-trigger" role="tab" aria-controls="information-part" id="information-part-trigger" aria-selected="true" >
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
                        <div id="step_2" class="content active" role="tabpanel">

                            <form method="POST" action="{{route('secretary.meeting.manage.minutes.create.step2_p',\Instantiation::instance())}}">
                                @csrf

                                <input type="hidden" name="meeting_request" value="{{$meeting_request->id ?? ''}}"/>

                                <div class="form-row">

                                    <div class="form-group col-md-6">
                                        <label for="$signature_sheet">Asociar hoja de firmas</label>

                                        @if($meeting_request == null)

                                            <select id="signature_sheet" class="selectpicker form-control @error('signature_sheet') is-invalid @enderror" name="signature_sheet" value="{{ old('signature_sheet') }}" autofocus>

                                                <option value="">
                                                    No asociar ninguna hoja de firmas
                                                </option>

                                                @foreach($signature_sheets as $signature_sheet)
                                                    <option {{$signature_sheet->id == old('$signature_sheet') ? 'selected' : ''}} value="{{$signature_sheet->id}}">
                                                        {!! $signature_sheet->title !!}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <small class="form-text text-muted">
                                                Asocia una hoja de firmas al acta.
                                            </small>

                                        @else

                                            @if($meeting_request->signature_sheet == null)

                                                <select id="$signature_sheet" class="selectpicker form-control @error('$signature_sheet') is-invalid @enderror" name="$signature_sheet" value="{{ old('$signature_sheet') }}" autofocus>

                                                    <option value="">
                                                        No asociar ninguna hoja de firmas
                                                    </option>

                                                    @foreach($signature_sheets as $signature_sheet)
                                                        <option {{$signature_sheet->id == old('$signature_sheet') ? 'selected' : ''}} value="{{$signature_sheet->id}}">
                                                            {!! $signature_sheet->title !!}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <small class="form-text text-muted">
                                                    Asocia una hoja de firmas al acta.
                                                </small>

                                            @else
                                                <br>
                                                Esta convocatoria ya tiene asociada una hoja de firmas.
                                                <input type="hidden" name="signature_sheet" value="{{$meeting_request->signature_sheet->id}}"/>
                                            @endif

                                        @endif


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



@endsection
