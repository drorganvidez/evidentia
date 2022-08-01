
    <div class="col-lg-6" wire:ignore>

        <form method="POST" id="form">

            @csrf

            <input type="hidden" name="_id" value="{{$evidence_temp_id}}">
            <input type="hidden" name="points_to" value="{{$evidence_temp->points_to}}">

            <div class="row order-md-1 order-sm-1">

                <x-input>
                    <x-slot:col>
                        col-lg-6 col-md-6 col-sm-12
                    </x-slot:col>
                    <x-slot:order>
                        order-md-1 order-sm-1
                    </x-slot:order>
                    <x-slot:label>
                        Título
                    </x-slot:label>
                    <x-slot:name>
                        title
                    </x-slot:name>
                    <x-slot:value>
                        {{$evidence_temp->title ?? ''}}
                    </x-slot:value>
                    <x-slot:required></x-slot:required>
                    <x-slot:autofocus></x-slot:autofocus>
                </x-input>

                <x-input>
                    <x-slot:col>
                        col-6 col-md-3
                    </x-slot:col>
                    <x-slot:order>
                        order-md-2 order-sm-2
                    </x-slot:order>
                    <x-slot:label>
                        Horas
                    </x-slot:label>
                    <x-slot:name>
                        hours
                    </x-slot:name>
                    <x-slot:value>
                        {{\Time::complex_shape_hours($evidence_temp->hours ?? '')}}
                    </x-slot:value>
                </x-input>

                <x-input>
                    <x-slot:col>
                        col-6 col-md-3
                    </x-slot:col>
                    <x-slot:order>
                        order-md-3 order-sm-3
                    </x-slot:order>
                    <x-slot:label>
                        Minutos
                    </x-slot:label>
                    <x-slot:name>
                        minutes
                    </x-slot:name>
                    <x-slot:value>
                        {{\Time::complex_shape_minutes($evidence_temp->hours ?? '')}}
                    </x-slot:value>
                </x-input>

            </div>

            <div class="row order-md-2 order-sm-2">

                <x-select>
                    <x-slot:data>
                        {{$committees}}
                    </x-slot:data>
                    <x-slot:col>
                        col-lg-6 col-md-6 col-sm-12
                    </x-slot:col>
                    <x-slot:order>
                        order-md-4 order-sm-4
                    </x-slot:order>
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
                    </x-slot:value>
                    <x-slot:info>
                        <p>
                            Es <b>obligatorio</b> asociar tu evidencia a algún comité existente.
                        </p>
                        <p>
                            Asociando tu evidencia a un comité permitirá al coordinador correspondiente aceptar o rechazarla.
                        </p>
                        <p class="mb-0">
                            Ten en cuenta que existe un tiempo desde que mandas tu evidencia a revisión y
                            tu coordinador la acepta (o rechaza). Considera mandar tus evidencias con tiempo suficiente.
                        </p>
                    </x-slot:info>
                </x-select>

            </div>

            <div class="row order-md-3 order-sm-3">

                <x-select>
                    <x-slot:data>
                        {{$students}}
                    </x-slot:data>
                    <x-slot:col>
                        col-12 col-md-12
                    </x-slot:col>
                    <x-slot:order>
                        order-md-5 order-sm-5
                    </x-slot:order>
                    <x-slot:label>
                        Estudiante asociado
                    </x-slot:label>
                    <x-slot:option_name>
                        full_name_with_username
                    </x-slot:option_name>
                    <x-slot:name>
                        guest_id
                    </x-slot:name>
                    <x-slot:value>
                        {{$evidence_temp->guest->id ?? ''}}
                    </x-slot:value>
                    <x-slot:default>
                        Sin estudiante asociado
                    </x-slot:default>
                    <x-slot:info>
                        <p>
                            Si has trabajo de forma conjunta con otro estudiante, puedes asociarlo a esta evidencia. Las horas que
                            definas para esta evidencia serán contabilizadas para ambos. Será como si cada uno hubiera redactado
                            la evidencia por separado.
                        </p>
                        <p>Esto evitará que existan evidencias por duplicado en el sistema, agilizando la corrección y minimizando
                            los errores humanos.</p>
                        <p class="mb-0">
                            <b>
                                Ten en cuenta que hasta que la evidencia no haya sido aceptada, al estudiante
                                asociado no le aparecerá en su cuenta.
                            </b>
                        </p>
                    </x-slot:info>
                </x-select>

            </div>

            <div class="row order-md-4 order-sm-4">

                <x-textarea>
                    <x-slot:col>
                        col-lg-12 col-md-12 col-sm-12
                    </x-slot:col>
                    <x-slot:order>
                        order-md-6 order-sm-6
                    </x-slot:order>
                    <x-slot:label>
                        Descripción
                    </x-slot:label>
                    <x-slot:name>
                        description
                    </x-slot:name>
                    <x-slot:value>
                        {!! $evidence_temp->description ?? '' !!}
                    </x-slot:value>
                    <x-slot:description>
                        Escribe una descripción concisa de tu evidencia (entre 10 y 20.000 caracteres)
                    </x-slot:description>
                </x-textarea>

            </div>

            <div class="row order-md-6 order-sm-6">

                <div class="form-group col-md-12 order-md-8 order-sm-8">

                    <div id="loading" style="display: none">
                        <div class="spinner-grow spinner-grow-sm" role="status">
                        </div>
                        Guardado automático...

                    </div>

                    <div id="loaded" style="display: none">
                        <i class="fe fe-save"></i> Último guardado: <span id="datetime"></span>
                    </div>

                </div>

                <div class="form-group col-12 order-9 order-md-9 order-sm-9">
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

                    function saveGuest(){
                        let guest_id = $( "select[name=guest_id]").val();
                        Livewire.emit('saveGuest', guest_id)
                    }

                    function saveDescription(){
                        let description = $("input[name=description]").val();
                        Livewire.emit('saveDescription', description)
                    }

                    function toSave(){

                        if(must_save){
                            $("#loaded").hide();
                            $("#loading").show();

                            var today = new Date();
                            var datetime = today.getDate() + '/' + ( today.getMonth() + 1 ) + '/' + today.getFullYear() + " " + today.getHours() + ':' + today.getMinutes() + ':' + today.getSeconds();
                            $("#datetime").html(datetime);
                            
                            saveTitle();
                            saveHours();
                            saveCommittee();
                            saveDescription();
                            saveGuest();

                            setTimeout(() => {

                                $("#loading").hide();
                                $("#loaded").show();

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

