@extends('layouts.app')


@section('title', 'Generar diploma')


@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')
    
    <form method="GET" enctype="multipart/form-data" )>

        <x-id :id="$evidence->id ?? ''" :edit="$edit ?? ''"/>



        <div class="row">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                <div class="widget-user-header text-white" style="background: url(http://localhost/dist/img/abstract.jpg); height: 50px" bis_skin_checked="1">

                                    <h5 class="widget-user-desc text-left" style="margin-bottom: 0px; margin-top: 12px; margin-left: 7px;">
                                        <i class="fas fa-pencil-alt"></i>
                                        &nbsp;&nbsp;
                                        Formulario
                                    </h5>

                                </div>

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="5" attr="nombreDiploma" :value="$evidence->title ?? ''" label="Nombre del Diploma" description="Nombre del archivo pdf que contendrá el diploma"/>
                            <div class="form-group col-md-5">
                                <label for="name">Nombre del premiado</label>
                                <input id="name" type="ctype_alpha" class="form-control" placeholder="Jose Luis Gomez Parejo" name="name"   description="Nombre de la persona premiada" required>
                                <small class="form-text text-muted">Nombre del premiado</small>
                                @error("name")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> 

                            <div class="form-group col-md-5">
                                <label for="mailto">Mail del Premiado/a</label>
                                <input id="mailto" type="email" class="form-control" placeholder="joseluis32@gmail.com" name="mailto"   description="Correo electrónico de la persona premiada" required>
                                <small class="form-text text-muted">Mail del Premiado</small>
                                @error("name")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <x-input col="5" attr="course" :value="$evidence->title ?? ''" label="Curso realizado" description="Nombre del curso que ha realizado"/>
                            
                            <div class="form-group col-md-5">
                                <label>Seleccionar diploma a generar</label>
                                <select id="certificates" name="diplomaGenerar" class="selectpicker form-control">
                                    @foreach($certificates as $c)
                                        <option value='{{$c->html}}'> {{$c->title ?? ''}} </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Selecciona el diploma que quieres generar con los datos del formulario.</small>

                            </div>                            
                            
                            <div class="form-group col-md-5">
                                <label for="score">Puntuación</label>
                                <input id="score" type="number" min="0" max="10" class="form-control" placeholder="1-10" name="score" value="$evidence->score ?? ''"  step="1" description="Puntuación obtenida por el ganador en el curso" required>
                                <small class="form-text text-muted">Enteros</small>
                                @error("score")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> 

                            <div class="form-group col-md-5">
                                                <label for="date">Fecha</label>
                                                <input id="date" type="date"
                                                       class="form-control @error('date') is-invalid @enderror" name="date"
                                                       @if(old('date'))
                                                       value="{{old('date')}}"
                                                       @else
                                                       value=""
                                                       @endif
                                                       autofocus
                                                       placeholder="02/10/2021"
                                                       required>
                                                <small class="form-text text-muted">Fecha de generación del diploma.
                                                </small>

                                                @error('date')
                                                <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                                @enderror
                                            </div>



                            <div class="form-group col-md-4">
                                <button type="button"  formaction="https://generador-diplomas-innosoft.herokuapp.com/diploma" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default" style = "margin-top:60px; margin-left: 120px"><i class="fas fa-external-link-square-alt"></i> &nbsp;Generar diploma</button>
                            </div>

                            <div class="modal fade" id="modal-default">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Generar diploma</h4>
                                        </div>
                                       <!-- <div class="modal-body">
                                            <p>Cuando se genera un diploma, este se envía al email de la persona premiada
                                                por lo que
                                                <b>no podrá ser eliminado.</b></p>
                                            <p>¿Deseas continuar?</p>
                                        </div> -->
                                        <div class="modal-footer justify-content-between">
                                           <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button> -->
                                            <button type="submit" formaction="http://127.0.0.1:5000/diploma" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Sí, generar diploma</button>
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
