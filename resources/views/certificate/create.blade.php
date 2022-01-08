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

                            
                            <div class="form-group col-md-5">
                                <label for="nombreDiploma">Nombre del diploma generado</label>
                                <input id="nombreDiploma" type="string" minlength= 10 class="form-control" placeholder="diploma_joseluis" name="nombreDiploma"  description="Nombre del archivo pdf que contendrá el diploma" required>
                                <small class="form-text text-muted">Nombre del diploma a generar</small>
                                @error("nombreDiploma")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> 
                            
                            <div class="form-group col-md-5">
                                <label for="name">Nombre del premiado</label>
                                <input id="name" type="ctype_alpha" minlength= 10 class="form-control" placeholder="Jose Luis Gomez Parejo" name="name"   description="Nombre de la persona premiada" required>
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

                            
                            <div class="form-group col-md-5">
                                <label for="course">Nombre del curso realizado</label>
                                <input id="course" type="string" class="form-control" placeholder="Ciberseguridad III: inicio" name="course"   description="Nombre del curso que ha realizado" required>
                                <small class="form-text text-muted">Curso realizado</small>
                                @error("course")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> 

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
                                <input id="score" type="string" placeholder="nº1" class="form-control"  name="score"  description="Evaluación obtenida por el ganador del diploma" required>
                                <small class="form-text text-muted">Evaluación obtenida por el ganador del diploma</small>
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
                                <button type="submit"  formaction="{{$route_publish}}" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default" style = "margin-top:60px; margin-left: 120px"><i class="fas fa-external-link-square-alt"></i> &nbsp;Generar diploma</button>
                            </div>

                        </div>

                    </div>

                </div>
                </div>
                <div class="widget-user-header text-white" style="background: url(http://localhost/dist/img/abstract.jpg); height: 395px; width:250px; margin-top:75px; margin-left:40px" bis_skin_checked="1">
                    <img src="https://institucional.us.es/innosoft/wp-content/uploads/2018/10/logo_2_negro-e1540204473260.png" style="margin-left: 85px; margin-top:90px">
                    <h5 class="widget-user-desc text-left" style="margin-bottom: 30px; margin-top: 40px; margin-left: 33px; font-size:xx-large; ">
                        
                        
                        Innosoft Days 
                        &nbsp;&nbsp;&nbsp;&nbsp;2021-2022
                    </h5>

                </div>
                
            </div>
            
</div>
        
        
        </div>
        
    </form>


    
    @section('scripts')

    

    @endsection

@endsection
