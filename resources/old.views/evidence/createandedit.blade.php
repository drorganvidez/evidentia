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

    @isset($edit)

        <div class="row">
            <div class="col-lg-3 col-sm-12">
                <p>
                    <button class="btn btn-info btn-block" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        <i class="fas fa-eraser"></i> Ver todas las ediciones
                    </button>
                </p>
            </div>
        </div>

        <div class="collapse" id="collapseExample">

                <div class="row">

                    @foreach($evidence->flow_evidences() as $evidence_i)

                        <div class="col-lg-3 mb-0 pb-0">

                            @if(Request()->route('id') == $evidence_i->id)
                                <div class="small-box bg-info">
                            @else
                                <div class="small-box bg-light">
                            @endif

                                <div class="inner">
                                    <h5>{{ Str::limit($evidence_i->title, 30) }}</h5>

                                    {{ \Carbon\Carbon::parse($evidence_i->created_at)->diffForHumans() }}

                                    ({{$evidence_i->created_at->format('d/m/y H:i:s')}})

                                </div>

                                @if(Request()->route('id') == $evidence_i->id)
                                        <a href="#" class="small-box-footer disabled">Actualmente editando</a>
                                @else
                                    <a href="{{route('evidence.edit',['instance' => $instance, 'id' => $evidence_i->id])}}" class="small-box-footer"><i class="fas fa-pencil-alt"></i> Continuar edición</a>
                                @endif


                            </div>

                        </div>

                    @endforeach

                </div>

        </div>
    @endisset

    <form method="POST" enctype="multipart/form-data">
        @csrf

        <x-id :id="$evidence->id ?? ''" :edit="$edit ?? ''"/>

        <input type="hidden" name="removed_files" id="removed_files"/>


        <div class="row">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="5" attr="title" :value="$evidence->title ?? ''" label="Título" description="Escribe un título que describa con precisión tu evidencia (mínimo 5 caracteres)"/>

                            <div class="form-group col-md-2">
                                <label for="hours">Horas</label>
                                <input id="" type="number" min="0" max="99" class="form-control" placeholder="" name="hours" value="{{\Time::complex_shape_hours($evidence->hours ?? '')}}" autocomplete="hours" autofocus="" step="0.01">
                                <small class="form-text text-muted">Enteros o decimales</small>
                                @error("hours")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label for="minutes">Minutos</label>
                                <input id="" type="number" min="0" max="60" class="form-control" placeholder="" name="minutes" value="{{\Time::complex_shape_minutes($evidence->hours ?? '') }}" autocomplete="minutes" autofocus="">
                                <small class="form-text text-muted">Enteros</small>
                                @error("minutes")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label for="comittee">Comité asociado</label>
                                <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                                    @foreach($comittees as $comittee)
                                        @isset($evidence)
                                            <option {{$comittee->id == old('comittee') || $evidence->comittee->id == $comittee->id ? 'selected' : ''}} value="{{$comittee->id}}">
                                        @else
                                            <option {{$comittee->id == old('comittee') ? 'selected' : ''}} value="{{$comittee->id}}">
                                                @endisset
                                                {!! $comittee->name !!}
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

                            <x-textarea col="12" attr="description" :value="$evidence->description ?? ''"
                                        label="Descripción de la evidencia"
                                        description="Escribe una descripción concisa de tu evidencia (entre 10 y 20000 caracteres)."
                            />

                            <div class="form-group col-md-4">
                                <button type="submit" formaction="{{$route_draft}}" class="btn btn-secondary btn-block"><i class="fas fa-pencil-ruler"></i> &nbsp;Guardar como borrador</button>
                            </div>

                            <div class="form-group col-md-4">
                                <button type="button"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar evidencia</button>
                            </div>

                            <div class="modal fade" id="modal-default">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Publicar una evidencia</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Cuando se publica una evidencia, esta se envía al coordinador de tu comité
                                                para su posterior revisión. Mientras esté en proceso de revisión,
                                                <b>no podrá ser editada.</b></p>
                                            <p>¿Deseas continuar?</p>
                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" formaction="{{$route_publish}}" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Sí, publicar evidencia</button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <label>Adjuntar pruebas</label>

                        <input type="file" name="files[]" id="files" multiple>

                    </div>

                </div>

            </div>

        </div>

    </form>

    @section('scripts')

        <script>

            setInterval(function () {
                $(".filepond--file-info-main").each(function() {
                    var uri = $(this).text();
                    $( this ).text(decodeURI(uri));
                });
            },1);

            // plugins de interés
            FilePond.registerPlugin(FilePondPluginFileValidateSize);
            FilePond.registerPlugin(FilePondPluginFileValidateType);

            FilePond.create(
                document.querySelector('input[id="files"]'),
                {
                    maxFileSize: 50000000,
                    maxTotalFileSize: 200000000,
                    labelMaxTotalFileSizeExceeded: 'Tamaño total máximo excedido',
                    labelMaxFileSizeExceeded: 'El archivo es demasiado grande',
                    labelMaxFileSize: 'El tamaño máximo es de {filesize}',
                    labelMaxTotalFileSize: 'El tamaño máximo total es de {filesize}',
                    acceptedFileTypes: ['image/*','application/zip','application/x-7z-compressed','application/x-tar','application/msword','application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '.xlsx','application/pdf','application/x-rar-compressed','application/vnd.ms-powerpoint','application/vnd.oasis.opendocument.presentation','application/vnd.oasis.opendocument.spreadsheet','application/vnd.oasis.opendocument.text'],
                    labelFileTypeNotAllowed: 'Tipo de archivo no válido',
                    server: {
                        url: '{{route('upload.process',Instantiation::instance())}}',
                        process: {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                        },
                        load: (source, load, error, progress, abort, headers) => {

                            var request = new Request(decodeURI(source));
                            fetch(request).then(function(response) {

                                response.blob().then(function(myBlob) {

                                    load(myBlob);

                                    $(".filepond--file-info-main").each(function() {
                                        var uri = $(this).text();
                                        $( this ).text(decodeURI(uri));
                                    });

                                });
                            });

                            $(".filepond--file-info-main").each(function() {
                                var uri = $(this).text();
                                $( this ).text(decodeURI(uri));
                            });

                        },
                        remove: function(source, load, errorCallback) {
                            var filename = source.split('/').pop()
                            var url = location.origin + '/' + '{{\Instantiation::instance()}}' + '/evidence/upload/remove/' + filename;
                            var request = new Request(url);

                            fetch(request).then(function(response) {
                                console.log(response);
                            });

                            load();
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    },

                    files: [
                        @foreach(Filepond::getFilesFromTemporaryFolder() as $file_name)

                            {
                                source: '{{route('upload.load',['instance' => Instantiation::instance(), 'file_name' => $file_name])}}',
                                options: {
                                    type: 'local'
                                }
                            },

                        @endforeach
                    ]
                }
            );

        </script>

    @endsection

@endsection


