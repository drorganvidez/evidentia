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

                        <div class="step">
                            <button type="button" class="step-trigger" role="tab" aria-controls="information-part" id="information-part-trigger" aria-selected="true" disabled="disabled">
                                <span class="bs-stepper-circle" style="background-color: #1aa179">2</span>
                                <span class="bs-stepper-label">Asociar asistencias</span>
                            </button>
                        </div>

                        <div class="line"></div>

                        <div class="step active">
                            <button type="button" class="step-trigger" role="tab" aria-controls="information-part" id="information-part-trigger" aria-selected="true" >
                                <span class="bs-stepper-circle">3</span>
                                <span class="bs-stepper-label">Redactar acta</span>
                            </button>
                        </div>

                    </div>

                    <div class="bs-stepper-content">

                        <div id="step_3" class="content active" role="tabpanel">

                            <form method="POST" action="{{route('secretary.meeting.manage.minutes.create.step3_p',\Instantiation::instance())}}">
                                @csrf

                                <h4>Información de la reunión</h4>

                                @if($meeting_request)
                                    <div class="callout callout-info">
                                        <p>La información que detallaste en la convocatoria se ha volcado automáticamente en el formulario.</p>
                                        <p>Recuerda rellenar <b>las horas y los minutos empleados</b> en la reunión.</p>
                                    </div>
                                @endif

                                <div class="form-row">

                                    <x-input col="6" attr="title" :value="$meeting_request->title ?? ''" label="Título" description="Escribe un título para tu reunión."/>

                                    <div class="form-group col-md-3">
                                        <label for="date">Día</label>
                                        <input id="date" type="date"
                                               class="form-control @error('date') is-invalid @enderror" name="date"
                                               @if(old('date'))
                                               value="{{old('date')}}"
                                               @else

                                                   @if($meeting_request != null)
                                                        value="{{\Carbon\Carbon::parse($meeting_request->datetime)->format('Y-m-d')}}"
                                                    @else
                                                        value=""
                                                   @endif


                                               @endif
                                               required autofocus>
                                        <small class="form-text text-muted">Indica el día de la reunión.
                                        </small>

                                        @error('date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="time">Hora</label>
                                        <input id="time" type="time"
                                               class="form-control @error('time') is-invalid @enderror" name="time"
                                               @if(old('time'))
                                               value="{{old('time')}}"
                                               @else

                                               @if($meeting_request != null)
                                               value="{{\Carbon\Carbon::parse($meeting_request->datetime)->format('H:i')}}"
                                               @else
                                               value=""
                                               @endif


                                               @endif
                                               required autofocus>
                                        <small class="form-text text-muted">Indica la hora de la reunión.
                                        </small>

                                        @error('time')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                </div>

                                <div class="form-row">

                                    <x-input col="6" attr="place" :value="$meeting_request->place ?? ''" label="Lugar" description="Indica el lugar de la reunión."/>

                                    <div class="form-group col-md-3">
                                        <label for="type">Tipo de reunión</label>
                                        <select id="type" class="selectpicker form-control @error('type') is-invalid @enderror" name="type" value="{{ old('type') }}" required autofocus>

                                            @isset($meeting_request)
                                                <option {{$meeting_request->type  == old('type') || $meeting_request->type == 'ORDINARY' ? 'selected' : ''}} value="1">ORDINARIA</option>
                                                <option {{$meeting_request->type  == old('type') || $meeting_request->type == 'EXTRAORDINARY' ? 'selected' : ''}} value="2">EXTRAORDINARIA</option>
                                            @else
                                                <option value="1">ORDINARIA</option>
                                                <option value="2">EXTRAORDINARIA</option>
                                            @endisset

                                        </select>

                                        <small class="form-text text-muted">Elige el tipo de reunión.</small>

                                        @error('type')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="modality">Tipo de modalidad</label>
                                        <select id="modality" class="selectpicker form-control @error('modality') is-invalid @enderror" name="modality" value="{{ old('modality') }}" required autofocus>

                                            @isset($meeting_request)
                                                <option {{$meeting_request->modality  == old('modality') || $meeting_request->modality == 'F2F' ? 'selected' : ''}} value="1">PRESENCIAL</option>
                                                <option {{$meeting_request->modality  == old('modality') || $meeting_request->modality == 'TELEMATIC' ? 'selected' : ''}} value="2">TELEMÁTICA</option>
                                                <option {{$meeting_request->modality  == old('modality') || $meeting_request->modality == 'MIXED' ? 'selected' : ''}} value="2">HÍBRIDA</option>
                                            @else
                                                <option value="1">PRESENCIAL</option>
                                                <option value="2">TELEMÁTICA</option>
                                                <option value="2">HÍBRIDA</option>
                                            @endisset

                                        </select>

                                        <small class="form-text text-muted">Elige la modalidad de reunión.</small>

                                        @error('type')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                </div>

                                <div class="form-row">

                                    <div class="form-group col-md-3">
                                        <label for="hours">Horas invertidas</label>
                                        <input id="" type="number" class="form-control" placeholder="" name="hours" value="{{\Time::complex_shape_hours($meeting->hours ?? '')}}" autocomplete="hours" autofocus="" step="0.01">
                                        <small class="form-text text-muted">Enteros o decimales</small>
                                        @error("hours")
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="minutes">Minutos invertidos</label>
                                        <input id="" type="number" min="0" max="60" class="form-control" placeholder="" name="minutes" value="{{\Time::complex_shape_minutes($meeting->hours ?? '') }}" autocomplete="minutes" autofocus="">
                                        <small class="form-text text-muted">Enteros</small>
                                        @error("minutes")
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                </div>

                                <hr>

                                <h4>Asistencias</h4>

                                @if($signature_sheet)
                                    <div class="callout callout-info">
                                        <p>Las asistencias firmadas se han volcado automáticamente en el formulario.</p>
                                    </div>
                                @endif

                                <div class="form-row">

                                    <div class="form-group col-md-6">
                                        <label for="defaultlist">Elige una lista predeterminada</label>
                                        <select id="defaultlist" onchange="getLista(this);" class="selectpicker form-control @error('defaultlist') is-invalid @enderror" value="{{ old('type') }}" required autofocus>

                                            <option value="-1">Selecciona una lista</option>
                                            @foreach($defaultlists as $defaultlist)
                                                <option value="{{trim($defaultlist->id)}}">{{trim($defaultlist->name)}}</option>
                                            @endforeach

                                        </select>

                                        <small class="form-text text-muted">
                                            Puedes elegir una lista predeterminada.

                                            @if($signature_sheet)
                                                Ten en cuenta que esto <b>eliminará</b> las asistencias volcadas de la hoja de firmas.
                                            @endif

                                        </small>
                                        @error('defaultlist')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label>Seleccionar alumnos</label>
                                        <select id="users" name="users[]" class="duallistbox" multiple="multiple @error('users') is-invalid @enderror">
                                            @foreach($users as $user)
                                                <option

                                                    @isset($signature_sheet)
                                                        @if($signature_sheet->users->contains($user))
                                                        selected
                                                        @endif
                                                    @endisset

                                                    {{$user->id == old('user') ? 'selected' : ''}} value="{{$user->id}}">
                                                    {{trim($user->surname)}}, {{trim($user->name)}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('users')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                </div>

                                <hr>

                                <h4>Acuerdos tomados</h4>

                                @if($meeting_request)
                                    <div class="callout callout-info">
                                        <p>Los puntos de la orden del día se han volcado automáticamente en el formulario.</p>
                                    </div>
                                @endif

                                @foreach($meeting_request->diary->diary_points as $diary_point)
                                    punto: {{$diary_point->point}}
                                @endforeach

                                <div class="form-row">

                                    <div class="form-group col-md-6">
                                        <input id="" type="text" class="form-control" placeholder="" name="title" value="Nueva convocatoria" required="" autocomplete="title" autofocus="">
                                        <small class="form-text text-muted">Punto del día</small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <textarea class="textarea form-control name=" required></textarea>
                                    </div>

                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            </div>


        </div>
    </div>

@section('scripts')

    <script>

        function getLista(sel){

            if(sel.value != -1) {

                var demo = $('.duallistbox').bootstrapDualListbox();
                $("#users").empty();

                var seleccionados = new Array();

                $.ajax({
                    url: '/{{$instance}}/secretary/meeting/defaultlist/' + sel.value,
                    success: function (respuesta) {

                        for (var i = 0; i < respuesta.length; i++) {
                            demo.append('<option value="'+respuesta[i].id+'" selected>'+respuesta[i].surname+', '+respuesta[i].name+'</option>')
                            seleccionados.push(respuesta[i].id);
                        }

                        // Volvemos a incluir todos los usuarios
                        $.ajax({
                            url: '/{{$instance}}/gp/users/all/',
                            success: function (respuesta) {

                                for (var i = 0; i < respuesta.length; i++) {
                                    if(seleccionados.indexOf(respuesta[i].id) == -1){
                                        demo.append('<option value="'+respuesta[i].id+'">'+respuesta[i].surname+', '+respuesta[i].name+'</option>')
                                    }

                                }

                                demo.bootstrapDualListbox('refresh');

                            },
                            error: function () {
                                console.log("No se ha podido obtener la información de todos los usuarios");
                            }
                        });

                    },
                    error: function () {
                        console.log("No se ha podido obtener la información de la lista predeterminada");
                    }
                });

            }
        }

    </script>

@endsection


@endsection
