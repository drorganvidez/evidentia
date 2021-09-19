@extends('layouts.app')

@section('title', 'Crear convocatoria')

@section('title-icon', 'far fa-handshake')

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

                <div class="card-body">

                    <form method="POST" action="{{route('secretary.meeting.manage.request.new',\Instantiation::instance())}}" id="request_form">
                        @csrf

                        <input type="hidden" name="points_list" id="points_list"/>

                        <x-id :id="$request->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="title" :value="$request->title ?? ''" label="Título" description="Escribe un título para tu reunión."/>


                            <div class="form-group col-md-3">
                                <label for="date">Día programado</label>
                                <input id="date" type="date"
                                       class="form-control @error('date') is-invalid @enderror" name="date"
                                       @if(old('date'))
                                       value="{{old('date')}}"
                                       @else
                                       @isset($edit)
                                       value="{{\Carbon\Carbon::parse($request->datetime)->format('Y-m-d')}}"
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
                                <label for="time">Hora programada</label>
                                <input id="time" type="time"
                                       class="form-control @error('time') is-invalid @enderror" name="time"
                                       @if(old('time'))
                                       value="{{old('time')}}"
                                       @else
                                       @isset($edit)
                                       value="{{\Carbon\Carbon::parse($request->datetime)->format('H:i')}}"
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

                            <x-input col="6" attr="place" :value="$request->place ?? ''" label="Lugar" description="Indica el lugar de la reunión."/>

                            <div class="form-group col-md-3">
                                <label for="type">Tipo</label>
                                <select id="type" class="selectpicker form-control @error('type') is-invalid @enderror" name="type" value="{{ old('type') }}" required autofocus>

                                    @isset($request)
                                        <option {{$request->type  == old('type') || $request->type == '1' ? 'selected' : ''}} value="1">ORDINARIA</option>
                                        <option {{$request->type  == old('type') || $request->type == '2' ? 'selected' : ''}} value="2">EXTRAORDINARIA</option>
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
                                <label for="modality">Modalidad</label>
                                <select id="modality" class="selectpicker form-control @error('modality') is-invalid @enderror" name="modality" value="{{ old('modality') }}" required autofocus>

                                    @isset($request)
                                        <option {{$request->type  == old('modality') || $request->type == '1' ? 'selected' : ''}} value="1">PRESENCIAL</option>
                                        <option {{$request->type  == old('modality') || $request->type == '2' ? 'selected' : ''}} value="2">TELEMÁTICA</option>
                                        <option {{$request->type  == old('modality') || $request->type == '2' ? 'selected' : ''}} value="2">HÍBRIDA</option>
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


                            <div class="col-lg-6">

                                <label for="title">Orden del día</label>

                                <div class="input-group">
                                    <input type="text" class="form-control" id="add_point">
                                    <span class="input-group-append">
                                        <button id="button_add_point" type="button" class="btn btn-primary">Añadir</button>
                                    </span>
                                </div>

                                <small class="form-text text-muted">Añade un punto del día. Puedes modificar el orden de los puntos cuanto desees.</small>

                                <br>

                                <ul class="todo-list ui-sortable" data-widget="todo-list" id="points">

                                </ul>

                                <br>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit"  class="btn btn-primary btn-block">Crear convocatoria</button>
                            </div>
                        </div>


                    </form>
                </div>

            </div>


        </div>
    </div>

    @section('scripts')

        <script id="point_template" type="text/x-handlebars-template">

            <li class="rounded" style="" id="@{{id}}">

                <span class="handle ui-sortable-handle">
                  <i class="fas fa-ellipsis-v"></i>
                  <i class="fas fa-ellipsis-v"></i>
                </span>

                <span class="text point_title">@{{title}}</span>

                <div class="tools">
                    <i class="fas fa-trash" onclick="remove(@{{id}})"></i>
                </div>
            </li>

        </script>

        <script>
            $(document).ready(function(){

                var form = $("#request_form");

                form.submit(function (event){

                    // añadimos los puntos al envío
                    var points = [];
                    $(".point_title").each(function(){
                        points.push($(this).html())
                        $("#points_list").val(JSON.stringify(points));
                    });

                   return true;
                });

                $("#add_point").keypress(function(e) {

                    var code = (e.keyCode ? e.keyCode : e.which);

                    if(code == 13){
                        e.preventDefault();
                        add_point();
                        return false;
                    }
                });

            });

            function add_point(){
                var source = document.getElementById("point_template").innerHTML;
                var template = Handlebars.compile(source);
                var context = { title: $("#add_point").val(), id: make_id(16)};
                var html = template(context);
                $("#points").append(html);
                $("#add_point").val("");
            }

            $("#button_add_point").click(function(){
                add_point();
            })

            function remove(id){
                $("#"+id).remove();
            }


        </script>

    @endsection


@endsection
