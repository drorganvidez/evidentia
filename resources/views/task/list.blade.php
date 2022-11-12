@extends('layouts.app')

@section('title', 'Mis tareas')

@section('title-icon', 'fas fa-id-badge')

@section('breadcrumb')
    
    <li class="breadcrumb-item active">@yield('title')</li>
    
@endsection

@section('content')

    
    <!-- BOTON PARAR FUTURA VERSION
    <div class="form-group col-md-4">
        <button type="button" style = "width:auto; background-color:#dc3545; border-color:#dc3545;" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default">
            <i class="fas fa-clock"></i>
         &nbsp;Parar tarea</button>
    </div>
    -->
    <div class="row">
    
    <div class="col-lg-12">

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="form-row">

                <x-input col="3" attr="title" :value="$task->title ?? ''" label="Título"/>
                <x-input col="4" type="text" attr="description" :value="$task->title ?? ''" label="Descripción"/>

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
                    <div id="duration">
                        00:00:00
                    </div>
                </div> 
                
                <div class="form-group col-md-2">
                    <label style="visibility:hidden">Start Button</label>
                    <button type="button" style = "width:auto;" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default">
                        <i class="fas fa-clock"></i>
                    &nbsp;Empezar tarea</button>
                </div>
                
            </div>


        </div>
    </div>

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
                        <!-- AÑADIR FUNCION LISTAR TAREAS -->
                        @foreach($tasks as $task)
                            <tr>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$task->id}}</td>
                                <td style="display:block;text-overflow: ellipsis;width: 12vw;overflow: hidden; white-space: nowrap;"><a href="{{route('task.view',['instance' => $instance, 'id' => $task->id])}}">{{$task->title}}</a></td>
                                <td></td>
                                <td style="display:block;text-overflow: ellipsis;width: 30vw;overflow: hidden; white-space: nowrap;" class="">{{$task->description}}</td>
                                <td style="text-align:center;" class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$task->hours}}</td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">12:00 - 15:00</td>
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

    @section('scripts')

        

    @endsection

@endsection
