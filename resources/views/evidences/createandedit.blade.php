@extends('layouts.app')

@section('title', 'Evidencias')

@section('submenu')

    <x-submenus.evidences-menu/>

@endsection

@section('content')

    <form method="POST">

        @csrf

        <input type="hidden" name="_id" value="{{$evidence_temp->id}}">

        <div class="row">

            <div class="col-lg-6">



                <div class="row">

                    <x-input>
                        <x-slot:col>
                            col-6 col-md-6
                        </x-slot:col>
                        <x-slot:label>
                            Título
                        </x-slot:label>
                        <x-slot:name>
                            title
                        </x-slot:name>
                        <x-slot:value>
                            {{$evidence->title ?? ''}}
                        </x-slot:value>
                        <x-slot:required></x-slot:required>
                        <x-slot:autofocus></x-slot:autofocus>
                    </x-input>

                    <x-input>
                        <x-slot:col>
                            col-6 col-md-3
                        </x-slot:col>
                        <x-slot:label>
                            Horas
                        </x-slot:label>
                        <x-slot:name>
                            hours
                        </x-slot:name>
                        <x-slot:value>
                            {{\Time::complex_shape_hours($evidence->hours ?? '')}}
                        </x-slot:value>
                    </x-input>

                    <x-input>
                        <x-slot:col>
                            col-6 col-md-3
                        </x-slot:col>
                        <x-slot:label>
                            Minutos
                        </x-slot:label>
                        <x-slot:name>
                            minutes
                        </x-slot:name>
                        <x-slot:value>
                            {{\Time::complex_shape_minutes($evidence->hours ?? '')}}
                        </x-slot:value>
                    </x-input>

                </div>

                <div class="row">

                    <x-select>
                        <x-slot:data>
                            {{$committees}}
                        </x-slot:data>
                        <x-slot:col>
                            col-6 col-md-6
                        </x-slot:col>
                        <x-slot:label>
                            Comité asociado
                        </x-slot:label>
                        <x-slot:option_name>
                            name
                        </x-slot:option_name>
                        <x-slot:name>
                            committee_id
                        </x-slot:name>
                    </x-select>

                </div>

                <div class="row">

                    <x-textarea>
                        <x-slot:col>
                            col-12 col-md-12
                        </x-slot:col>
                        <x-slot:label>
                            Descripción
                        </x-slot:label>
                        <x-slot:name>
                            description
                        </x-slot:name>
                        <x-slot:value>
                            {{$evidence->description ?? ''}}
                        </x-slot:value>
                        <x-slot:description>
                            Escribe una descripción concisa de tu evidencia (entre 10 y 20.000 caracteres)
                        </x-slot:description>
                    </x-textarea>

                </div>

                <div class="row">

                    <div class="form-group col-md-6">
                        <button type="submit" formaction="{{$route_draft}}" class="btn btn-secondary btn-block"><i class="fas fa-pencil-ruler"></i> &nbsp;Guardar como borrador</button>
                    </div>

                </div>

            </div>

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

    </form>


@endsection
