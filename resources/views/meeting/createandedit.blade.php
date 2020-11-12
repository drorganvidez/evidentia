@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar reunión: '.$meeting->title)
@else
    @section('title', 'Crear nueva reunión')
@endisset

@section('title-icon', 'far fa-handshake')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('secretary.meeting.list',['instance' => $instance])}}">Gestionar reuniones</a></li>
    @endisset
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::meetings_timestamp()"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{$route}}">
                        @csrf

                        <x-id :id="$meeting->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="title" :value="$meeting->title ?? ''" label="Título" description="Escribe un título para tu reunión."/>


                            <div class="form-group col-md-3">
                                <label for="date">Día</label>
                                <input id="date" type="date"
                                       class="form-control @error('date') is-invalid @enderror" name="date"
                                       @if(old('date'))
                                       value="{{old('date')}}"
                                       @else
                                       @isset($edit)
                                       value="{{\Carbon\Carbon::parse($meeting->datetime)->format('Y-m-d')}}"
                                       @endisset

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
                                       @isset($edit)
                                       value="{{\Carbon\Carbon::parse($meeting->datetime)->format('H:i')}}"
                                       @endisset

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

                            <x-input col="6" attr="place" :value="$meeting->place ?? ''" label="Lugar" description="Indica el lugar de la reunión."/>

                            <div class="form-group col-md-2">
                                <label for="hours">Horas invertidas</label>
                                <input id="" type="number" class="form-control" placeholder="" name="hours" value="{{\Time::complex_shape_hours($meeting->hours ?? '')}}" autocomplete="hours" autofocus="" step="0.01">
                                <small class="form-text text-muted">Enteros o decimales</small>
                                @error("hours")
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>


                            <div class="form-group col-md-2">
                                <label for="minutes">Minutos invertidos</label>
                                <input id="" type="number" min="0" max="60" class="form-control" placeholder="" name="minutes" value="{{\Time::complex_shape_minutes($meeting->hours ?? '') }}" autocomplete="minutes" autofocus="">
                                <small class="form-text text-muted">Enteros</small>
                                @error("minutes")
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label for="type">Tipo de reunión</label>
                                <select id="type" class="selectpicker form-control @error('type') is-invalid @enderror" name="type" value="{{ old('type') }}" required autofocus>

                                    @isset($meeting)
                                        <option {{$meeting->type  == old('type') || $meeting->type == '1' ? 'selected' : ''}} value="1">ORDINARIA</option>
                                        <option {{$meeting->type  == old('type') || $meeting->type == '2' ? 'selected' : ''}} value="2">EXTRAORDINARIA</option>
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

                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label for="defaultlist">Elige una lista predeterminada</label>
                                <select id="defaultlist" onchange="getLista(this);" class="selectpicker form-control @error('defaultlist') is-invalid @enderror" value="{{ old('type') }}" required autofocus>

                                    <option value="-1">Selecciona una lista</option>
                                    @foreach($defaultlists as $defaultlist)
                                        <option value="{{trim($defaultlist->id)}}">{{trim($defaultlist->name)}}</option>
                                    @endforeach

                                </select>

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

                                            @isset($meeting)
                                            @if($meeting->users->contains($user))
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

                        <div class="form-row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit"  class="btn btn-primary btn-block">Guardar reunión</button>
                            </div>
                        </div>


                    </form>
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
