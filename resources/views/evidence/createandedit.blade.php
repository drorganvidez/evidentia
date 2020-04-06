@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar evidencia: '.$evidence->title)
@else
    @section('title', 'Crear evidencia')
@endisset

@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('evidence.list',$instance)}}">Mis evidencias</a></li>
    @endisset
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        @csrf

                        <x-id :id="$evidence->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="title" :value="$evidence->title ?? ''" label="Título" description="Escribe un título que describa con precisión tu evidencia (mínimo 5 caracteres)"/>

                            <x-input col="3" attr="hours" :value="$evidence->hours ?? ''" type="number" step="0.01" label="Horas" description="Números enteros o decimales."/>

                            <div class="form-group col-md-3">
                                <label for="comittee">Comité</label>
                                <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                                    @foreach($comittees as $comittee)
                                        <option {{$comittee->id == old('comittee') ? 'selected' : ''}} value="{{$comittee->id}}">
                                            {{$comittee->comittee}}

                                            @if(isset($comittee->subcomittee))
                                                ({{$comittee->subcomittee}})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                <small class="form-text text-muted">Elige un comité al que quieres asociar tu evidencia.</small>

                                @error('comite')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <x-textarea col="6" attr="description" :value="$evidence->description ?? ''"
                                        label="Descripción de la evidencia"
                                        description="Escribe una descripción concisa de tu evidencia (entre 10 y 20000 caracteres)."
                            />

                            <x-input col="6" attr="files[]" id="files" type="file"  label="Adjuntar archivos" description="Adjunta archivos que respalden tu evidencia y el número de horas empleadas."/>


                        </div>

                        <div class="row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit" formaction="{{$route_publish}}" class="btn btn-primary btn-block"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar evidencia</button>
                            </div>
                            <div class="col-lg-3 mt-1">
                                <button type="submit" formaction="{{$route_draft}}" class="btn btn-secondary btn-block"><i class="fas fa-pencil-ruler"></i> &nbsp;Guardar como borrador</button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
