@extends('layouts.app')

@if($evidence_temp->points_to == null)
    @section('title', 'Evidencias')
@else
    @section('subtitle', 'Evidencias')
    @section('title', 'Editando: ' . $evidence_temp->title)
@endif



@if($evidence_temp->points_to == null)
    @section('submenu')

        <x-submenus.evidences-menu/>

    @endsection
@endif

@section('content')

    @if($evidence_temp->points_to != null)

        <div class="row">

            <div class="col-lg-12">
                <a href="{{route('evidences.draft', \Instantiation::instance())}}" class="btn btn-outline-primary mb-4">
                   <i class="fe fe-skip-back"></i> Volver a mis evidencias
                </a>
            </div>

        </div>

    @endif

        <div class="row">

            @php

            if(!isset($points_to))
                $points_to = null;
            @endphp

            <livewire:save-evidence
                    :evidence_temp="$evidence_temp"
                    :evidence_temp_id="$evidence_temp_id"
                    :route_draft="$route_draft"
                    :route_publish="$route_publish"
                    :committees="$committees"
                    :students="$students"
            ></livewire:save-evidence>

            <div class="col-lg-6 order-md-7 order-sm-7">

                <div class="form-group">

                    <!-- Label -->
                    <label class="form-class mb-3">
                        Subir archivos
                    </label>

                    <livewire:upload-files evidence_id="{{$evidence_temp_id}}"></livewire:upload-files>

                </div>


            </div>

            <div class="modal" id="modal_evidence_temp" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-card card">
                            <div class="card-header">

                                <!-- Title -->
                                <h4 class="card-header-title" id="exampleModalCenterTitle">
                                   <i class="fe fe-save"></i>  Autoguardado
                                </h4>

                            </div>
                            <div class="card-body">

                                <p>Hemos encontrado una versión reciente guardada el {{ \Carbon\Carbon::parse($evidence_temp->updated_at)->format('d/m/Y')}} a las {{\Carbon\Carbon::parse($evidence_temp->updated_at)->format('H:i:s')}} </p>
                                <p>¿Deseas continuar escribiendo desde este guardado?</p>

                                <button class="btn btn-success mt-2" onclick="close_modal()">
                                    Sí, continuar
                                </button>

                                <form method="post" class="d-inline" action="{{route("evidences.delete.autosaved",\Instantiation::instance())}}">
                                    @csrf

                                    <input type="hidden" name="_id" value="{{$evidence_temp->id}}">

                                    <button type="submit" class="btn btn-outline-danger mt-2">
                                        No, borrar y empezar de nuevo
                                    </button>


                                </form>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @push('scripts')

                <script type="module">

                    function close_modal(){
                        $('#modal_evidence_temp').hide();
                    }

                    $(document).ready(function() {

                        @if($evidence_temp->autosaved
                            and empty(old('title'))
                            and empty(old('hours'))
                            and empty(old('minutes'))
                            and empty(old('description')))

                          $('#modal_evidence_temp').show();

                        @endif
                    });


                </script>

            @endpush



        </div>



@endsection
