@extends('layouts.app')

@section('title', 'Importaciones')
@section('title-icon', 'nav-icon fas fa-file-import')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-4">

            <div class="card shadow-lg">

                <div class="card-body">

                    <h4>Subir archivo XLS</h4>

                    <div id="loading" style="display: none">
                        Se están importando los usuarios. No cierres esta página.
                        <div class="overlay">
                            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                        </div>



                    </div>

                    <div id="load_xls">

                        <ul>

                            <li>Puedes importar un XLS con todos los alumnos y alumnas de este curso de una sola vez.</li>

                            <li>Por defecto, todos los nuevos usuarios tendrán el rol de
                                <span class="badge badge-pill badge-secondary">Estudiante
                            </span></li>

                            <li>Una vez importados los usuarios, el XLS se borrará del sistema.</li>

                        </ul>

                        <form id="request_form" method="POST" enctype="multipart/form-data" action="{{$route}}">

                            @csrf

                            <div class="row">

                                <div class="col-12">
                                    <input type="file" name="files[]" id="files" multiple>
                                </div>

                                <div class="col-lg-12 col-md-12 mt-4">
                                    <button type="submit"  class="btn btn-primary btn-block"><i class="nav-icon fas fa-file-import"></i>&nbsp;Importar alumnos</button>
                                </div>

                            </div>

                        </form>

                    </div>




                </div>

            </div>

        </div>

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Importación masiva de alumnos</h4>

                    <p>Con objeto de garantizar un buen funcionamiento de esta herramienta, se recomienda
                        que el archivo XLS siga la siguiente disposición de columnas:</p>

                    <div class="table-responsive">
                        <table class="table table-hover m-0">
                            <thead>
                            <tr>
                                <th scope="col">dni</th>
                                <th scope="col">apellidos</th>
                                <th scope="col">nombre</th>
                                <th scope="col">uvus</th>
                                <th scope="col">grupo</th>
                                <th scope="col">email</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr scope="row">
                                <td>77777777</td>
                                <td>Polo Polo</td>
                                <td>Marco</td>
                                <td>marpolpol</td>
                                <td>Grupo 1</td>
                                <td>polo@mail.com</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <p>Como consejos de interés:</p>

                    <ul>
                        <li>Las columnas se tienen que llamar <b>exactamente igual</b> a como aparecen ahí.</li>
                        <li>No subir XLS con celdas combinadas.</li>
                        <li>Los campos <i>DNI, UVUS, EMAIL</i> deben ser <b>únicos</b>.</li>
                    </ul>


                </div>

            </div>
        </div>

    </div>

    @section('scripts')

        <script>

            $(document).ready(function () {
                var form = $("#request_form");

                form.submit(function (event){

                    $("#load_xls").hide();
                    $("#loading").show();

                    return true;

                });
            });

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
                    maxFiles: 1,
                    maxFileSize: 50000000,
                    maxTotalFileSize: 200000000,
                    labelMaxTotalFileSizeExceeded: 'Tamaño total máximo excedido',
                    labelMaxFileSizeExceeded: 'El archivo es demasiado grande',
                    labelMaxFileSize: 'El tamaño máximo es de {filesize}',
                    labelMaxTotalFileSize: 'El tamaño máximo total es de {filesize}',
                    acceptedFileTypes: [
                        'application/msword',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        '.xlsx',
                        '.xls'
                    ],
                    labelFileTypeNotAllowed: 'Tipo de archivo no válido',
                    server: {
                        url: '{{route('xls.upload.process',Instantiation::instance())}}',
                        process: {
                            url: '/',
                            method: 'POST'
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
                            var url = location.origin + '/' + '{{\Instantiation::instance()}}' + '/xls/upload/remove/' + filename;
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
