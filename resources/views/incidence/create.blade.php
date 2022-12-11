@extends('layouts.app')

@section('title', 'Crear incidencia')

@section('title-icon', 'fas fa-exclamation')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{ $instance ?? '' }}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <form method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="5" attr="title" :value="$incidence->title ?? ''" label="Título" description="Escribe un título que describa con precisión tu incidencia (mínimo 5 caracteres)"/>
                        

                            <div class="form-group col-md-3">
                                <label for="comittee">Comité asociado</label>
                                <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                                    @foreach($comittees as $comittee)
                                        @isset($incidence)
                                            <option {{$comittee->id == old('comittee') || $incidence->comittee->id == $comittee->id ? 'selected' : ''}} value="{{$comittee->id}}">
                                        @else
                                            <option {{$comittee->id == old('comittee') ? 'selected' : ''}} value="{{$comittee->id}}">
                                                @endisset
                                                {!! $comittee->name !!}
                                            </option>
                                            @endforeach
                                </select>

                                <small class="form-text text-muted">Elige un comité al que quieres asociar tu incidencia.</small>

                                @error('comite')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <x-textarea col="12" attr="description" :value="$incidence->description ?? ''"
                                        label="Descripción de la incidencia"
                                        description="Escribe una descripción concisa de tu incidencia (entre 10 y 20000 caracteres)."
                            />

                            <div class="form-group col-md-4">
                                <button type="button"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar incidencia</button>
                            </div>

                            <div class="modal fade" id="modal-default">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Publicar una incidencia</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Cuando se publica una incidencia, esta se envía al coordinador de tu comité
                                                para su posterior revisión.Despues que esté en proceso de revisión,
                                                <b>no podrá ser borrada.</b></p>
                                            <p>¿Deseas continuar?</p>
                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" formaction="{{$route_publish}}" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Sí, publicar incidencia</button>
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
                            var url = location.origin + '/' + '{{\Instantiation::instance()}}' + '/incidence/upload/remove/' + filename;
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


