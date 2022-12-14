@extends('layouts.app')

@section('title', 'Mis tareas')

@section('title-icon', 'fas fa-id-badge')

@section('breadcrumb')
    
    <li class="breadcrumb-item active">@yield('title')</li>
    
@endsection

@section('content')
    <div class="row">

    <form method="POST" enctype="multipart/form-data" novalidate style="display:in-line;"> 
    @csrf
    <div class="col-lg-12">

        <div class="card shadow-sm">

            <div class="card-body">

                <div class="form-row">
                    <x-input col="3" id="title" name="title" attr="title" :value="$task->title ?? ''" label="Título"/>
                    <x-input col="4" id="description" name="description" type="text" attr="description" :value="$task->description ?? ''" label="Descripción"/>
                    <input id="start_date" style="display:none;" type="datetime-local" name="start_date" />
                    <input id="end_date" type="datetime-local" style="display:none;" name="end_date" />

                    <div class="form-group col-md-2">
                        <label for="comittee">Comité asociado</label>
                        <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                            @foreach($comittees as $comittee)
                                @isset($task)
                                    <option {{$comittee->id == old('comittee') || $task->comittee->id == $comittee->id ? 'selected' : ''}} value="{{$comittee->id}}">
                                @else
                                    <option {{$comittee->id == old('comittee') ? 'selected' : ''}} value="{{$comittee->id}}">
                                        @endisset
                                        {!! $comittee->name !!}
                                    </option>
                                    @endforeach
                        </select>

                        @error('comite')
                        <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group col-md-1">
                        <label for="duration">Duración</label>
                        <div >
                            <p id="duration" style="font-size:21px"></p>
                        </div>
                    </div> 
                    
                    <div id="div_start_button" class="form-group col-md-2">
                        <label style="visibility:hidden">Start Button</label>
                        <button type="button" style = "width:auto;" id="start_chronometrer"class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default">
                            <i class="fas fa-clock"></i>
                        &nbsp;Empezar tarea</button>
                    </div>
                    <div style ="display:none;" id="div_stop_button" class="form-group col-md-2">
                        <label style="visibility:hidden">Stop Button</label>
                        <button type="submit" formaction="{{$route_new}}" style = "background-color:#dc3545; border-color:#dc3545;width:auto;" id="stop_chronometrer" class="btn btn-primary btn-block">
                            <i class="fas fa-clock"></i>
                        &nbsp;Parar tarea</button>
                    </div>
                </div>


            </div>
        </div>
    </form>

    <div class="col-lg-12">

    <div class="card shadow-sm">

        <div class="card-body">
            <h2>Lista de tareas</h2>

            <div class="col-md-12">

                <table id="dataset" class="table table-hover table-responsive col-md-12">
                    <thead>
                    <tr>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
                        <th>Título</th>
                        <th></th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Descripción</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Horas</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Inicio-Fin</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$task->id}}</td>
                                <td style="display:block;text-overflow: ellipsis;width: 12vw;overflow: hidden; white-space: nowrap;"><a href="{{route('task.view',['instance' => $instance, 'id' => $task->id])}}">{{$task->title}}</a></td>
                                <td></td>
                                <td style="display:block;text-overflow: ellipsis;width: 30vw;overflow: hidden; white-space: nowrap;" class="">{{$task->description}}</td>
                                <td style="text-align:center;" class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$task->hours}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{substr($task->start_date,11,5)}} - {{substr($task->end_date,11,5)}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    <x-taskcomittee :task="$task"/>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
                <div class="col-lg-9 mt-12"></div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('task.list.export',['instance' => $instance, 'ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('task.list.export',['instance' => $instance, 'ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('task.list.export',['instance' => $instance, 'ext' => 'pdf'])}}"
                       class="btn btn-info btn-block" role="button">
                        PDF</a>
                </div>
            </div>

    @section('scripts')

    <script>
        
        let start_chronometrer = document.getElementById("start_chronometrer");
        let stop_chronometrer = document.getElementById("stop_chronometrer"); 
        let div_stop_button = document.getElementById("div_stop_button");
        let div_start_button = document.getElementById("div_start_button");
        let input_start_date = document.getElementById("start_date");
        let input_end_date = document.getElementById("end_date");
        let input_title = document.getElementById("title");
        let input_description = document.getElementById("description");
        let input_comittee = document.getElementById("comittee");
        let duration = document.getElementById("duration")
        let start_button = document.getElementById("start_chronometrer");

        start_chronometrer.onclick = start; 
        stop_chronometrer.onclick = stop; 

        if (sessionStorage.getItem("statusButton") == "block"){
            var actualDuration = sessionStorage.getItem("duration");
            duration.textContent = actualDuration
            h = actualDuration.slice(0,2)
            min = actualDuration.slice(3,5)
            sec = actualDuration.slice(6,8)
        }
        else{
            h = "00"
            min = "00"
            sec = "00"
            duration.textContent = h + ":" + min + ":" + sec
        }
        let hours = h,
        minutes = min,
        seconds = sec,
        chronometerDisplay = document.getElementById("duration"),
        chronometerCall

        window.onload = function(evento) {
            if (sessionStorage.getItem("statusButton") == "block"){    
                
                var title = sessionStorage.getItem("title");
                input_title.value = title

                var description = sessionStorage.getItem("description");
                input_description.value = description

                var start_date = sessionStorage.getItem("start_date");
                input_start_date.value = start_date
                
                var comittee = sessionStorage.getItem("comittee");
                input_comittee.value = comittee
                
                div_start_button.removeAttribute(`disabled`);
                sessionStorage.clear()
                start(evento);
            }
        }

        window.onbeforeunload = function() {
            clearInterval(chronometerCall);
            sessionStorage.setItem("title", input_title.value);
            sessionStorage.setItem("description", input_description.value);
            sessionStorage.setItem("start_date", input_start_date.value);
            sessionStorage.setItem("comittee", input_comittee.value);
            sessionStorage.setItem("statusButton", div_stop_button.style.display);
            sessionStorage.setItem("duration", duration.textContent);
        }


        function chronometer() {
            
            seconds ++
            if (seconds < 10) seconds = `0` + seconds
            if (seconds > 59) {
            seconds = `00`
            minutes ++
            if (minutes < 10) minutes = `0` + minutes
            }
            if (minutes > 59) {
            minutes = `00`
            hours ++
            if (hours < 10) hours = `0` + hours
            }
            chronometerDisplay.textContent = `${hours}:${minutes}:${seconds}`
        
        }

        function start(evento) {
            div_start_button.style.display = "none";
            div_stop_button.style.display = "block";
            chronometerCall = setInterval(chronometer, 1000);
            evento.target.setAttribute(`disabled`,``);
            let fecha_actual = new Date().toISOString().slice(0, 19).replace('T', ' ');
            input_start_date.setAttribute('value', fecha_actual.slice(0,16));
        }
        
        function stop(evento) {
            clearInterval(chronometerCall);
            let fecha_actual = new Date().toISOString().slice(0, 19).replace('T', ' ');
            input_end_date.setAttribute('value', fecha_actual.slice(0,16));
            
            start_button.disabled = true;
            start_button.textContent = "¡Cargando!";
            div_start_button.style.display = "block";
            div_stop_button.style.display = "none";
            
        }

    </script>

    @endsection

@endsection
