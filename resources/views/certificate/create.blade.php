@extends('layouts.app')


@section('title', 'Generar diploma')


@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <form method="GET" enctype="multipart/form-data">

        <x-id :id="$evidence->id ?? ''" :edit="$edit ?? ''"/>

        <!-- <input type="hidden" name="removed_files" id="removed_files"/> -->


        <div class="row">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="5" attr="nombreDiploma" :value="$evidence->title ?? ''" label="Nombre Diploma" description="Escribe un título que describa el diploma que vas a generar (mínimo 5 caracteres)"/>
                            <x-input col="5" attr="name" :value="$evidence->title ?? ''" label="Nombre del premiado" description="Escribe el nombre de la persona premiada"/>
                            <x-input col="5" attr="mailto" :value="$evidence->title ?? ''" label="Correo electrónico del premiado" description="Escribe el mail de la persona premiada"/>
                            <x-input col="5" attr="course" :value="$evidence->title ?? ''" label="Curso realizado" description="Escribe el mail de la persona premiada"/>
                            <!-- <x-selectfield col="5" attr="diplomaGenerar" slot="@foreach($certificates as $c) <option value='$c->html ?? ""'> {{$c->title ?? ''}} </option>@endforeach" label="Diploma a generar" description="Escribe el mail de la persona premiada" id="certificates"/>
 -->                        <div class="form-group col-md-5">
                                <label>Seleccionar diploma a generar</label>
                                <select id="certificates" name="diplomaGenerar" class="selectpicker form-control">
                                    @foreach($certificates as $c)
                                        <option value='{{$c->html}}'> {{$c->title ?? ''}} </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Selecciona el diploma que quieres generar con los datos del formulario.</small>
                                <!-- @error('users')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror -->
                            </div>
                            <x-input col="5" attr="score" :value="$evidence->title ?? ''" label="Puntuación" description="Escribe el mail de la persona premiada"/>
                            <x-input col="5" attr="date" :value="$evidence->title ?? ''" label="Fecha (10/2/12)" description="Escribe el mail de la persona premiada"/>

                            <!-- <div class="form-group col-md-2">
                                <label for="hours">Nombre de la persona</label>
                                <input id="" type="number" min="0" max="99" class="form-control" placeholder="" name="hours" value="{{\Time::complex_shape_hours($evidence->hours ?? '')}}" autocomplete="hours" autofocus="" step="0.01">
                                <small class="form-text text-muted">Enteros o decimales</small>
                                @error("hours")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> -->

                            <!-- <div class="form-group col-md-2">
                                <label for="minutes">Minutos</label>
                                <input id="" type="number" min="0" max="60" class="form-control" placeholder="" name="minutes" value="{{\Time::complex_shape_minutes($evidence->hours ?? '') }}" autocomplete="minutes" autofocus="">
                                <small class="form-text text-muted">Enteros</small>
                                @error("minutes")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> -->

                            

                            <!-- <x-textarea col="12" attr="description" :value="$evidence->description ?? ''"
                                        label="Descripción de la evidencia"
                                        description="Escribe una descripción concisa de tu evidencia (entre 10 y 20000 caracteres)."
                            /> -->


                            <div class="form-group col-md-4">
                                <button type="button"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar evidencia</button>
                            </div>

                            <div class="modal fade" id="modal-default">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Generar diploma</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Cuando se genera un diploma, este se envía al email de la persona premiada
                                                por lo que
                                                <b>no podrá ser eliminado.</b></p>
                                            <p>¿Deseas continuar?</p>
                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" formaction="http://127.0.0.1:5000/diploma" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Sí, publicar evidencia</button>
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

    @section('scripts')

    

    @endsection

@endsection


