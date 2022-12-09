@extends('layouts.app')

@section('title', 'Crear tarea')

@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
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

                            <x-id :id="$kanban->id ?? ''" :edit="$edit ?? ''"/>

                            <x-input col="5" attr="title" :value="$issue->title ?? ''" label="Título" description="Escribe un título que describa con precisión tu tarea (mínimo 5 caracteres)"/>

                            <x-textarea col="12" attr="description" :value="$issue->description ?? ''"
                                        label="Descripción de la tarea"
                                        description="Escribe una descripción concisa de tu tarea (entre 10 y 20000 caracteres)."
                            />

                            <div class="form-group col-md-2">
                                <label for="estimated_hours">Horas estimadas</label>
                                <input id="" type="number" min="0" max="99" class="form-control" placeholder="" name="estimated_hours" value="{{\Time::complex_shape_hours($issue->estimated_hours ?? '')}}" autocomplete="estimated_hours" autofocus="" step="0.01">
                                <small class="form-text text-muted">Enteros o decimales</small>
                                @error("hours")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            
                            <div class="form-group col-md-12">
                                <label>Seleccionar alumnos</label>
                                <select id="users" name="users[]" class="duallistbox" multiple="multiple @error('users') is-invalid @enderror">
                                    @foreach($users as $user)
                                        <option

                                            @isset($issue)
                                                @if($issue->users->contains($user))
                                                selected
                                                @endif
                                            @endisset

                                            {{$user->id == old('user') ? 'selected' : ''}} value="{{$user->id}}">
                                            {{trim($user->surname)}}, {{trim($user->name)}}
                                        </option>
                                    @endforeach
                                </select>
                                @error('users')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <button type="submit"  class="btn btn-primary btn-block" formaction="{{$route}}">Crear tarea</button>
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
                            var url = location.origin + '/' + '{{\Instantiation::instance()}}' + '/issue/upload/remove/' + filename;
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
                }
            );

        </script>

    @endsection
@endsection

