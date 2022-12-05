@extends('layouts.app')

@section('title', 'Crear tablero')

@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection



@section('content')

    <form method="POST" action="{{$route}}">
        @csrf
        <div class="row">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="5" attr="title" :value="$kanban->title ?? ''" label="Título" description="Escribe un título para tu tablero Kanban (mínimo 5 caracteres)"/>

                            <div class="form-group col-md-3">
                                <label for="comittee">Comité asociado</label>
                                <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                                    @foreach($comittees as $comittee)
                                        @isset($kanban)
                                            <option {{$comittee->id == old('comittee') || $evidence->comittee->id == $comittee->id ? 'selected' : ''}} value="{{$comittee->id}}">
                                        @else
                                            <option {{$comittee->id == old('comittee') ? 'selected' : ''}} value="{{$comittee->id}}">
                                                @endisset
                                                {!! $comittee->name !!}
                                            </option>
                                            @endforeach
                                </select>

                                <small class="form-text text-muted">Elige un comité al que quieres asociar tu tablero.</small>

                                @error('comite')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            

                            <div class="form-group col-md-4">
                                <button type="submit"  class="btn btn-primary btn-block" formaction="{{$route}}">Guardar tablero</button>
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
                            var url = location.origin + '/' + '{{\Instantiation::instance()}}' + '/kanban/upload/remove/' + filename;
                            var request = new Request(url);

                            fetch(request).then(function(response) {
                                console.log(response);
                            });

                            load();
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                }
            );

        </script>

    @endsection


@endsection