@extends('layouts.app')

@section('title', 'Editar tarea: '.$task->title)

@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('task.list',$instance)}}">Mis tareas</a></li>
    @endisset
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')


    <form method="POST" enctype="multipart/form-data">
        @csrf

        <x-id :id="$task->id ?? ''" :edit="$edit ?? ''"/>

        <input type="hidden" name="removed_files" id="removed_files"/>


        <div class="row">

            <div class="col-lg-10">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">
                            <x-input col="4" attr="title" :value="$task->title ?? ''" label="Título" description="Escribe un título que describa con precisión tu tarea"/>
                            <input style="display:none" name="id" value="{{$task->id}}"/>
                            <div class="form-group col-md-3">
                                <label for="start_date">Fecha de inicio</label>
                                <input id="start_date" type="datetime-local" class="form-control" name="start_date" value="{{$task->start_date}}" />
                                
                                <small class="form-text text-muted">Fecha y hora</small>
                                @error("start_date")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label for="end_date">Fecha fin</label>
                                <input id="end_date" type="datetime-local" class="form-control" name="end_date" value="{{$task->end_date}}" autocomplete="end_time" autofocus=""/>
                                <small class="form-text text-muted">Fecha y hora</small>
                                @error("end_date")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label for="comittee">Comité asociado</label>
                                <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                                    @foreach($comittees as $comittee)
                                        @isset($task)
                                            <option {{$comittee->id == old('comittee') || $task->comittee->id == $comittee->id ? 'selected' : ''}} value="{{$comittee->id}}">
                                        @else
                                            <option {{$comittee->id == old('comittee') ? 'selected' : ''}} value="{{$comittee->id}}">
                                                @endisset
                                                {!! $comittee->name !!}
                                            </option>
                                            @endforeach
                                </select>

                                <small class="form-text text-muted">Elige un comité al que quieres asociar tu tarea.</small>

                                @error('comite')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <x-textarea col="12" attr="description" :value="$task->description ?? ''"
                                        label="Descripción de la tarea"
                                        description="Escribe una descripción concisa de tu tarea (entre 10 y 20000 caracteres)."
                            />

                            <div class="form-group col-md-4">
                                <button type="submit" formaction="{{$route_save}}" class="btn btn-primary btn-block"><i class="fas fa-pencil-ruler"></i> &nbsp;Guardar tarea</button>
                            </div>


                        </div>

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
                            var url = location.origin + '/' + '{{\Instantiation::instance()}}' + '/task/upload/remove/' + filename;
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


