@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar evidencia: '.$instance->name)
@else
    @section('title', 'Crear evidencia')
@endisset

@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('admin.instance.manage')}}">Gestionar incidencias</a></li>
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

                        <x-id :id="$instance->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="title" :value="$evidence->name ?? ''" label="Título" description="Escribe un título que describa con precisión tu evidencia (mínimo 5 caracteres)"/>

                            <x-input col="3" attr="hours" :value="$evidence->hours ?? ''" type="number" step="0.01" label="Horas" description="Números enteros o decimales."/>

                            <x-select>

                                <x-slot name="col">3</x-slot>
                                <x-slot name="attr">comittee</x-slot>
                                <x-slot name="label">Comité</x-slot>
                                <x-slot name="description">Elige un comité al que quieres asociar tu evidencia.</x-slot>

                                @foreach($comittees as $comittee)
                                    <option {{$comittee->id == old('$comittee') ? 'selected' : ''}} value="{{$comittee->id}}">
                                        {{$comittee->comittee}}

                                        @if(isset($comittee->subcomitte))
                                            ({{$comittee->subcomitte}})
                                        @endif
                                    </option>
                                @endforeach

                            </x-select>

                            <x-textarea col="6" attr="description" :value="$evidence->description ?? ''"
                                        label="Descripción de la evidencia"
                                        description="Escribe una descripción concisa de tu evidencia (entre 10 y 20000 caracteres)."
                            />

                            <x-input col="6" attr="files[]" id="files" type="file"  label="Adjuntar archivos" description="Adjunta archivos que respalden tu evidencia y el número de horas empleadas."/>


                        </div>

                        <button type="submit" formaction="{{$route_draft}}" class="btn btn-primary"><i class="far fa-paper-plane"></i> Publicar evidencia</button>
                        <button type="submit" formaction="{{$route_draft}}" class="btn btn-light"><i class="fas fa-pencil-ruler"></i> Guardar evidencia como borrador</button>

                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
