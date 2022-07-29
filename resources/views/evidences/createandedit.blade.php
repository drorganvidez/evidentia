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



        </div>



@endsection
