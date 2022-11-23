@extends('layouts.app')


@section('title', 'Crear tarea Kanban')


@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('evidence.list',$instance)}}">Mis evidencias</a></li>
    @endisset
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection


@section('content')

<form method="POST" enctype="multipart/form-data">
        @csrf

        <x-id :id="$issue->id ?? ''" :edit="$edit ?? ''"/>

        <div class="row">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="5" attr="task" :value="$issue->task ?? ''" label="Tarea" description="Escribe un título que describa con precisión tu tarea"/>

                            <div class="form-group col-md-2">
                                <label for="hours">Horas</label>
                                <input id="" type="number" min="0" max="99" class="form-control" placeholder="" name="hours" value="{{\Time::complex_shape_hours($evidence->hours ?? '')}}" autocomplete="hours" autofocus="" step="0.01">
                                <small class="form-text text-muted">Enteros o decimales</small>
                                @error("hours")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label for="user">Alumno asociado</label>
                                <select id="user" class="selectpicker form-control @error('user') is-invalid @enderror" name="user" value="{{ old('user') }}" required autofocus>
                                    @foreach($users as $user)
                                        @isset($issue)
                                            <option {{$user->id == old('user') || $issue->user->id == $user->id ? 'selected' : ''}} value="{{$user->id}}">
                                        @else
                                            <option {{$user->id == old('user') ? 'selected' : ''}} value="{{$user->id}}">
                                                @endisset
                                                {!! $user->name, " ", $user->surname !!}
                                            </option>
                                            @endforeach
                                </select>

                                <small class="form-text text-muted">Elige el usuario al que quieres asociar tu tarea.</small>

                                @error('comite')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <x-textarea col="12" attr="description" :value="$issue->description ?? ''"
                                        label="Descripción de la tarea"
                                        description="Escribe una descripción concisa de tu tarea."
                            />

                            <div class="form-group col-md-4">
                                <button type="button"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar tarea</button>
                            </div>

                            <div class="modal fade" id="modal-default">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Publicar una evidencia</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Cuando se publica una tarea, esta se asigna a un usuario y ya 
                                                <b>no podrá ser editada.</b></p>
                                            <p>¿Deseas continuar?</p>
                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" formaction="{{$route_publish}}" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Sí, publicar tarea</button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

@endsection