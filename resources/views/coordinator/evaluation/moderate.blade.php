@extends('layouts.app')

@section('title', 'Moderando: ' . $evidence->title)

@section('options')
    <!-- Buttons -->
    <a href="{{route('evidences.export', ['instance' => \Instantiation::instance(), 'id' => $evidence->id])}}" class="btn btn-primary ms-2 lift">
        <i class="fe fe-download-cloud"></i> Exportar
    </a>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-6">
            <a href="{{route('coordinator.evidences.list', \Instantiation::instance())}}" class="btn btn-outline-primary mb-4">
                <i class="fe fe-skip-back"></i> Volver a las evidencias de mi comité
            </a>
        </div>

    </div>


    <div class="row">

        <div class="col-sm-12 col-lg-6 col-md-12 col-xl-6">

            <div class="row">

                <div class="col-12">

                    <!-- Content -->
                    <x-view-evidence :evidence="$evidence"></x-view-evidence>

                </div>

                <div class="col-12">

                    <x-view-files :evidence="$evidence"></x-view-files>

                </div>

            </div>


        </div>

        <div class="col-sm-12 col-lg-6 col-md-12 col-xl-6">

            <form method="POST" action="{{route('coordinator.evidences.moderate.evidence_p', \Instantiation::instance())}}">

                @csrf

                <input type="hidden" name="_id_evidence" value="{{$evidence->id}}">
                <input type="hidden" name="_id_review" value="{{$review?->id}}">

                <div class="row order-md-1 order-sm-1">

                    <div class="form-group">

                        <label class="form-class mb-1">
                            Puntuación
                        </label>

                        <small class="form-text text-muted">
                            ¿Qué puntuación, del 0 al 10, le das a esta evidencia?
                        </small>

                        @php



                        @endphp

                        @for ($i = 0; $i <= 10; $i++)

                            @php

                                $class = "btn btn-rounded-circle btn-outline-primary mb-2";

                            @endphp

                            @if(old('score'))

                                @php
                                if(strcmp(old("score"), $i) === 0){

                                    if(old("score") < 5){
                                        $class = "btn btn-rounded-circle btn-danger mb-2";
                                    }else{
                                        $class = "btn btn-rounded-circle btn-success mb-2";
                                    }
                                }
                                @endphp

                            @else

                                @php
                                    if(strcmp($review?->score, $i) === 0){

                                        if($review?->score < 5){
                                            $class = "btn btn-rounded-circle btn-danger mb-2";
                                        }else{
                                            $class = "btn btn-rounded-circle btn-success mb-2";
                                        }
                                    }
                                @endphp

                            @endif

                            <button type="button" id="score_{{ $i }}" onclick="select_score({{$i}})" class="{{$class}}">
                                <b>{{$i}}</b>
                            </button>
                        @endfor

                        @if(old('score'))

                            @php

                                $value = old('score');

                            @endphp

                        @else

                            @php

                                $value = $review?->score;

                            @endphp

                        @endif

                        <input type="hidden" name="score" id="score" class="form-control is-invalid" value="{{$value}}">

                        @error("score")
                        <div class="invalid-feedback">
                            {{$message}}
                        </div>
                        @enderror

                        <br>

                        @error("score")
                            <label class="form-class mb-1">
                                Estado
                            </label>
                        @else
                            <label class="form-class mb-1 mt-3">
                                Estado
                            </label>
                        @enderror

                        <br>

                        <span id="pending" class="badge bg-primary-soft">
                            Pendiente de revisar
                        </span>

                        <span id="accepted" class="badge bg-success-soft" style="display: none">
                            Aceptada
                        </span>

                        <span id="rejected" class="badge bg-danger-soft" style="display: none">
                            Rechazada
                        </span>



                    </div>

                </div>

                <div class="row order-md-4 order-sm-4">

                    <x-textarea>
                        <x-slot:col>
                            col-lg-12 col-md-12 col-sm-12
                        </x-slot:col>
                        <x-slot:label>
                            Comentario
                        </x-slot:label>
                        <x-slot:name>
                            comment
                        </x-slot:name>
                        <x-slot:value>
                            {!! $review?->comment ?? '' !!}
                        </x-slot:value>
                        <x-slot:description>
                            Escribe un comentario que aclare la puntuación que has dado (entre 10 y 20.000 caracteres)
                        </x-slot:description>
                    </x-textarea>

                </div>

                <div class="form-group">

                    <button type="submit" class="btn btn-primary bt-block mb-2">
                        <i class="fe fe-save"></i> &nbsp;Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

    @push('scripts')

        <script>

            $(document).ready(function(){

                @if(old('score'))
                    let previous_score = "{{old('score')}}";
                @else
                    let previous_score = "{{$review?->score}}";
                @endif


                previous_score = parseInt(previous_score);

                console.log(previous_score);

                if(!isNaN(previous_score)){
                    $("#pending").hide();
                    if(previous_score < 5){
                        $("#rejected").show();
                    }else{
                        $("#accepted").show();
                    }
                }

            });

            function select_score(i)
            {
                let score = parseInt(i);

                // clean
                for(let it = 0; it <= 10; it++){
                    $("#score_"+it).attr('class', 'btn btn-rounded-circle btn-outline-primary mb-2')
                }

                $("#pending").hide();
                $("#accepted").hide();
                $("#rejected").hide();

                if(i < 5){
                    $("#score_"+score).attr('class', 'btn btn-rounded-circle btn-danger mb-2')
                    $("#rejected").show();
                }else{
                    $("#score_"+score).attr('class', 'btn btn-rounded-circle btn-success mb-2')
                    $("#accepted").show();
                }

                $("#score").val(score);

            }

        </script>

    @endpush



@endsection
