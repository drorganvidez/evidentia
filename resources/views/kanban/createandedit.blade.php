@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar tablero: '.$kanban->title)
@else
    @section('title', 'Crear tablero')
@endisset

@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection




@section('content')

    @isset($edit)

    <div class="row">
        <div class="col-lg-3 col-sm-12">
            <p>
                <button class="btn btn-info btn-block" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                    <i class="fas fa-eraser"></i> 
                </button>
            </p>
        </div>
    </div>

    <div class="collapse" id="collapseExample">

            <div class="row">

                @foreach($kanban->flow_kanbans() as $kanban_i)

                    <div class="col-lg-3 mb-0 pb-0">

                        @if(Request()->route('id') == $kanban_i->id)
                            <div class="small-box bg-info">
                        @else
                            <div class="small-box bg-light">
                        @endif

                            <div class="inner">
                                <h5>{{ Str::limit($kanban->title, 30) }}</h5>
                            </div>

                            @if(Request()->route('id') == $kanban_i->id)
                                    <a href="#" class="small-box-footer disabled">Actualmente editando</a>
                            @else
                                <a href="{{route('kanban.edit',['instance' => $instance, 'id' => $kanban_i->id])}}" class="small-box-footer"><i class="fas fa-pencil-alt"></i> Continuar edición</a>
                            @endif


                        </div>

                    </div>

                @endforeach

            </div>

    </div>
    @endisset


    <form method="POST" enctype="multipart/form-data">
        @csrf

        <x-id :id="$kanban->id ?? ''" :edit="$edit ?? ''"/>

        <input type="hidden" name="removed_files" id="removed_files"/>


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
                                <button type="submit" formaction="{{$route_draft}}" class="btn btn-secondary btn-block"><i class="fas fa-pencil-ruler"></i> &nbsp;Guardar como borrador</button>
                            </div>

                            <div class="form-group col-md-4">
                                <button type="button"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar tablero</button>
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