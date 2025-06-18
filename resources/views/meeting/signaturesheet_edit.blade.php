@extends('layouts.app')

@section('title', 'Editar hoja de firmas: '.$signature_sheet->title)

@section('title-icon', 'fas fa-signature')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage')}}">Gestionar reuniones</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage.signaturesheet.list')}}">Mis hojas de firmas</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting/>

        <div class="col-md-8">

            <div class="card">

                <div class="card-body">

                    <div class="row">

                        <div class="col-lg-12">

                            <h4>
                                <i class="fas fa-child"></i> Convocatoria asociada
                            </h4>
                            @if($signature_sheet->meetingRequest)
                                <p>
                                    {{$signature_sheet->meetingRequest->title}}
                                    <br>
                                    {{ \Carbon\Carbon::parse($signature_sheet->meetingRequest->datetime)->format('d/m/Y') }}
                                    <br>
                                    {{ \Carbon\Carbon::parse($signature_sheet->meetingRequest->datetime)->format('H:i') }}
                                    <br>
                                    {{$signature_sheet->meetingRequest->place}}
                                </p>
                            @else
                                No hay ninguna convocatoria asociada a esta hoja de firmas.
                            @endif

                            <hr>

                        </div>

                    </div>



                    <form method="POST" action="{{route('secretary.meeting.manage.signaturesheet.save')}}">
                        @csrf

                        <x-id :id="$signature_sheet->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="title" :value="$signature_sheet->title ?? ''" label="Título" description="Escribe un título para tu hoja de firmas"/>

                            <div class="form-group col-md-6">
                                <label for="meeting_request">Asociar convocatoria</label>
                                <select id="meeting_request" class="selectpicker form-control @error('meeting_request') is-invalid @enderror" name="meeting_request_id" value="{{ old('meeting_request') }}" autofocus>

                                    <option value="">
                                        No modificar la convocatoria asociada
                                    </option>

                                    @foreach($available_meeting_requests as $meeting_request)
                                        <option {{$meeting_request->id == old('$meeting_request') ? 'selected' : ''}} value="{{$meeting_request->id}}">
                                            {!! $meeting_request->title !!}
                                        </option>
                                    @endforeach
                                </select>

                                <small class="form-text text-muted">Elige una convocatoria a la que quieres asociar tu hoja de firmas.
                                    Solo puedes asociar convocatorias que <b>no tengan</b> una hoja de firmas.</small>

                                @error('meeting_request')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-4">
                                <button type="submit"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default">
                                    <i class="fas fa-signature"></i> &nbsp;Actualizar hoja de firmas</button>
                            </div>

                        </div>



                    </form>
                </div>

            </div>


        </div>
    </div>


@endsection
