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

@section('info')
        <x-slimreminder :datetime="\Config::upload_evidences_timestamp()"/>
@endsection

@section('content')

    @isset($edit)

        <div class="row">
            <div class="col-lg-3 col-sm-12">
                <p>
                    <button class="btn btn-info btn-block" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        <i class="fas fa-eraser"></i> Ver ediciones anteriores
                    </button>
                </p>
            </div>
        </div>

        <div class="collapse" id="collapseExample">
            <div class="card card-body">

                <div class="row">

                    @foreach($evidence->flow_evidences() as $evidence_i)

                        <div class="col-lg-3">

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
        </div>
    @endisset

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="card">

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        @csrf

                        <x-id :id="$evidence->id ?? ''" :edit="$edit ?? ''"/>

                        <input type="hidden" name="removed_files" id="removed_files"/>

                        <div class="form-row">

                            <x-input col="6" attr="title" :value="$evidence->title ?? ''" label="Título" description="Escribe un título que describa con precisión tu evidencia (mínimo 5 caracteres)"/>

                            <x-input col="3" attr="hours" :value="$evidence->hours ?? ''" type="number" step="0.01" label="Horas invertidas" description="Números enteros o decimales."/>

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

                            <x-textarea col="6" attr="description" :value="$evidence->description ?? ''"
                                        label="Descripción de la evidencia"
                                        description="Escribe una descripción concisa de tu evidencia (entre 10 y 20000 caracteres)."
                            />

                            @isset($edit)

                                <div class="col-12 col-sm-6 col-lg-6">

                                    <label>Archivos asociados</label>

                                    <div class="card card-primary card-outline card-outline-tabs">
                                        <div class="card-header p-0 border-bottom-0">
                                            <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="custom-tabs-three-home-tab" data-toggle="pill" href="#attached_files" role="tab" aria-controls="custom-tabs-three-home" aria-selected="true">Archivos subidos</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill" href="#add_files" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">Subir más</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-body">
                                            <div class="tab-content" id="custom-tabs-three-tabContent">
                                                <div class="tab-pane fade show active" id="attached_files" role="tabpanel" aria-labelledby="custom-tabs-three-home-tab">
                                                    <div class="card-body table-responsive p-0">
                                                        <table class="table table-hover text-nowrap">
                                                            <thead>
                                                            <tr>
                                                                <th>Nombre</th>
                                                                <th>Tamaño</th>
                                                                <th>Opciones</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>

                                                            @foreach($evidence->proofs as $proof)


                                                                <tr id="file_{{$proof->file->id}}">
                                                                    <td>{{$proof->file->name}}</td>
                                                                    <td>{{$proof->file->sizeForHuman()}}</td>
                                                                    <td>
                                                                        <a class="btn btn-primary btn-sm" href="{{route('file.download',['instance' => $instance, 'id' => $proof->file->id])}}">
                                                                            <i class="fas fa-download"></i>
                                                                            Descargar
                                                                        </a>
                                                                        <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#modal-remove-{{$proof->file->id}}">
                                                                            <i class="fas fa-trash"></i>
                                                                            Eliminar
                                                                        </a>
                                                                        <div class="modal fade" id="modal-remove-{{$proof->file->id}}">
                                                                            <div class="modal-dialog modal-dialog-centered">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h4 class="modal-title">Eliminar archivo</h4>
                                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="modal-body">
                                                                                        <p>Este cambio no se puede deshacer.</p>
                                                                                        <p>¿Deseas continuar?</p>
                                                                                    </div>
                                                                                    <div class="modal-footer justify-content-between">
                                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                                                                        <button type="button"  onclick="remove_file({{$proof->file->id}})" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-trash"></i> &nbsp;Sí, eliminar archivo</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>

                                                                </tr>

                                                            @endforeach

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="add_files" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab">
                                                    <x-input col="12" attr="files[]" id="files" type="file" :required="false"
                                                             label="Adjuntar más archivos"
                                                             description="Adjunta más archivos que respalden tu evidencia y el número de horas empleadas."/>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.card -->
                                    </div>
                                </div>


                            @else
                                <x-input col="6" attr="files[]" id="files" type="file"  label="Adjuntar archivos" description="Adjunta archivos que respalden tu evidencia y el número de horas empleadas."/>
                            @endisset


                        </div>

                        <div class="row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit" formaction="{{$route_draft}}" class="btn btn-secondary btn-block"><i class="fas fa-pencil-ruler"></i> &nbsp;Guardar como borrador</button>
                            </div>
                            <div class="col-lg-3 mt-1">
                                <button type="button"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar evidencia</button>
                            </div>
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


                    </form>
                </div>

            </div>

        </div>
    </div>



    @isset($edit)

        @section('scripts')

        <script>

            function remove_file(id){
                $("#removed_files").val($("#removed_files").val() + "|" + id);
                $("#file_"+id).fadeOut(500);
            }

        </script>

        @endsection

    @endisset

@endsection
