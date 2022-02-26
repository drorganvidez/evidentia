@extends('layouts.app')

@section('title', 'Buzón de sugerencias')

@section('title-icon', 'fas fa-envelope-open-text')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">



        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">

                    <form action="{{$route}}" method="POST">
                        @csrf

                        <div class="form-row">

                            <div class="form-group col-lg-6">
                                <label for="subject">Asunto</label>
                                <select id="subject" class="selectpicker form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject') }}" required autofocus>

                                    <option selected value="Informar de bug encontrado">Informar de bug encontrado</option>
                                    <option value="Proponer una nueva funcionalidad">Proponer una nueva funcionalidad</option>
                                    <option value="Proponer una mejora">Proponer una mejora</option>
                                    <option value="Otro">Otro</option>

                                </select>

                                @error('subject')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                        </div>

                        <div class="form-row">

                            <x-textarea col="12" attr="comment"
                                        label="Comentario"
                            />

                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-3 col-sm-6 col-6 col-12">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i>&nbsp;&nbsp;Enviar</button>
                            </div>
                        </div>

                    </form>


                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h3>Ayúdanos a mejorar</h3>

                    <p>
                        ¿Has encontrado algo que no funciona como debería? ¿Crees que alguna funcionalidad se podría mejorar?
                        ¿Se te ocurren nuevas herramientas?
                    </p>

                    <p>
                        Te animamos a que hagas uso de este buzón de sugerencias para expresar tus inquietudes y opiniones.
                    </p>
                        <b>Es completamente anónimo.</b>
                    <p>

                    </p>
                </div>
            </div>

        </div>
    </div>


@endsection
