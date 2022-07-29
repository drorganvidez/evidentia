
    <div class="col-lg-6" wire:ignore>

        <form method="POST" id="form">

            @csrf

            <input type="hidden" name="_id" value="{{$evidence_temp->id}}">

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
                        {{$evidence_temp->title ?? ''}}
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
                        {{\Time::complex_shape_hours($evidence_temp->hours ?? '')}}
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
                        {{\Time::complex_shape_minutes($evidence_temp->hours ?? '')}}
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
                    <x-slot:value>
                        {{$evidence_temp->committee->id ?? ''}}
                        {{$evidence->committee?->id ?? ''}}
                    </x-slot:value>
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
                        {!! $evidence_temp->description ?? '' !!}
                        {!! $evidence->description ?? '' !!}
                    </x-slot:value>
                    <x-slot:description>
                        Escribe una descripción concisa de tu evidencia (entre 10 y 20.000 caracteres)
                    </x-slot:description>
                </x-textarea>

            </div>

            <div class="row">

                <div class="form-group col-md-12 ">

                    <div id="loading" style="display: none">
                        <div class="spinner-grow spinner-grow-sm" role="status">
                        </div>
                        Guardado automático...

                    </div>

                    <div id="loaded" style="display: none">
                        <i class="fe fe-save"></i> Último guardado: <span id="datetime"></span>
                    </div>

                </div>

                <div class="form-group col-12">
                    <button type="submit" formaction="{{$route_draft}}" class="btn btn-secondary bt-block mb-2">
                        <i class="fas fa-pencil-ruler"></i> &nbsp;Guardar como borrador</button>
                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modal_confirm">
                        <i class="fe fe-box"></i> Publicar evidencia
                    </button>
                </div>

                <div class="form-group col-sm-12 col-md-6">


                </div>

                <div class="modal fade" id="modal_confirm" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-card card">
                                <div class="card-header">

                                    <!-- Title -->
                                    <h4 class="card-header-title" id="exampleModalCenterTitle">
                                        ¿Estás segur@?
                                    </h4>


                                    <!-- Close -->
                                    <i style="cursor: pointer" class="fe fe-x-circle" data-bs-dismiss="modal" aria-label="Close"></i>
                                </div>
                                <div class="card-body">

                                    <p>Cuando se publica una evidencia, esta se envía al coordinador de tu comité
                                        para su posterior revisión. Mientras esté en proceso de revisión,
                                        <b>no podrá ser editada.</b></p>
                                    <p>¿Deseas continuar?</p>

                                    <button type="submit" formaction="{{$route_publish}}" class="btn btn-primary" data-toggle="modal" data-target="#modal_confirm">
                                        <i class="fas fa-external-link-square-alt"></i> &nbsp;Sí, publicar evidencia
                                    </button>


                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">
                                        Cancelar
                                    </button>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </form>

        @push('scripts')

            <script>
                $(document).ready(function(){

                    // we start to do automatic saving when we detect any change in the form
                    let must_save = false;

                    $("#form").change(function(){
                        must_save = true
                    })

                    $('.ql-editor').bind('DOMSubtreeModified', function(){
                        must_save = true
                    });

                    let interval = 1000*60;

                    function saveTitle(){
                        let title = $("input[name=title]").val();
                        Livewire.emit('saveTitle', title)
                    }

                    function saveHours(){
                        let hours = parseInt($("input[name=hours]").val()) + Math.floor(($("input[name=minutes]").val()*100)/60)/100;
                        Livewire.emit('saveHours', hours)
                    }

                    function saveCommittee(){
                        let committee_id = $( "select[name=committee_id]").val();
                        Livewire.emit('saveCommittee', committee_id)
                    }

                    function saveDescription(){
                        let description = $("input[name=description]").val();
                        Livewire.emit('saveDescription', description)
                    }

                    function toSave(){

                        if(must_save){
                            $("#loaded").hide();
                            $("#loading").show();
                            saveTitle();
                            saveHours();
                            saveCommittee();
                            saveDescription();
                            setTimeout(() => {

                                $("#loading").hide();
                                $("#loaded").show();

                                var today = new Date();
                                var datetime = today.getDate() + '/' + ( today.getMonth() + 1 ) + '/' + today.getFullYear() + " " + today.getHours() + ':' + today.getMinutes() + ':' + today.getSeconds();

                                $("#datetime").html(datetime);

                            }, 2000);
                        }

                    }

                    @if(old('title'))
                        must_save = true;
                        toSave();
                    @endif


                    setInterval(toSave, interval);

                })

            </script>

        @endpush


    </div>

