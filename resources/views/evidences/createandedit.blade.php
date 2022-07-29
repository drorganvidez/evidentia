@extends('layouts.app')

@section('title', 'Evidencias')

@section('submenu')

    <x-submenus.evidences-menu/>

@endsection

@section('content')

        <div class="row">

            @php

            if(!isset($evidence)){
                $evidence = null;
            }


            @endphp

            <livewire:save-evidence :evidence="$evidence" :evidence_temp="$evidence_temp" :route_draft="$route_draft" :route_publish="$route_publish" :committees="$committees"></livewire:save-evidence>

            <div class="col-lg-6">

                <div class="form-group">

                    <!-- Label -->
                    <label class="form-class mb-3">
                        Subir archivos
                    </label>

                    <livewire:upload-files evidence_id="{{$evidence_temp->id}}"></livewire:upload-files>

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

                <script>

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
