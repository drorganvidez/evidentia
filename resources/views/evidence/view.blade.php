@extends('layouts.app')

@section('title', 'Ver evidencia: '.$evidence->title)

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR') and $evidence->comittee->id == \Illuminate\Support\Facades\Auth::user()->coordinator->comittee->id)
        <li class="breadcrumb-item"><a href="{{route('coordinator.evidence.list.all',$instance)}}">Gestionar evidencias de {{\Illuminate\Support\Facades\Auth::user()->coordinator->comittee->name}}</a></li>
    @else
        <li class="breadcrumb-item"><a href="{{route('evidence.list',$instance)}}">Mis evidencias</a></li>
    @endif

    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-6 order-2 order-md-1">
                            <div class="row">
                                <div class="col-12">


                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>
                                                <x-evidencecomittee :evidence="$evidence"/>
                                                <span class="badge badge-secondary">
                                                    <i class="far fa-clock"></i> {{$evidence->hours}} horas
                                                </span>
                                            </h5>

                                        </div>
                                        <div>

                                        </div>
                                    </div>

                                    <h4>{{$evidence->title}}</h4>

                                    <div class="post text-justify">
                                        {!! $evidence->description !!}

                                        <br><br>



                                        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR') and $evidence->comittee->id == \Illuminate\Support\Facades\Auth::user()->coordinator->comittee->id)

                                            <x-evidencemanagecoordinator :instance="$instance" :evidence="$evidence" />

                                        @else

                                            @if($evidence->status == 'DRAFT' and !\Carbon\Carbon::now()->gt(\Config::upload_evidences_timestamp()))
                                                <a class="btn btn-info btn-sm"
                                                   href="{{route('evidence.edit',['instance' => $instance, 'id' => $evidence->id])}}">
                                                    <i class="fas fa-pencil-alt">
                                                    </i>
                                                    Editar
                                                </a>
                                            @endif

                                            @if(!\Carbon\Carbon::now()->gt(\Config::upload_evidences_timestamp()))
                                                <x-buttonconfirm :id="$evidence->id" route="evidence.remove" title="¿Seguro?" description="Esto borrará la evidencia actual, las
                                                ediciones anteriores <b>y todos los archivos adjuntos.</b>" type="REMOVE"/>
                                            @endif

                                        @endif


                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-6 order-1 order-md-2">

                            <div class="row">

                                <div class="col-lg-4">


                                        <div class="text-muted">
                                            <p class="text-sm">Última edición
                                                <b class="d-block">{{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }}</b>
                                            </p>
                                        </div>
                                </div>

                                <div class="col-lg-8 mt-1">
                                    <x-evidencestatus :evidence="$evidence"/>
                                </div>

                            </div>


                            @if($evidence->status == 'DRAFT' and !\Carbon\Carbon::now()->gt(\Config::upload_evidences_timestamp()))
                                <h5 class="text-muted mt-2">Ediciones anteriores</h5>

                                <div class="card-body table-responsive p-0" style="height: 200px;">
                                    <table class="table text-nowrap table-borderless ">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Edición</th>
                                        <th>Opciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($evidence->previous_evidences() as $evidence_i)


                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($evidence_i->created_at)->diffForHumans() }}</td>
                                            <td><a class="btn btn-info btn-sm"
                                                   href="{{route('evidence.edit',['instance' => $instance, 'id' => $evidence_i->id])}}">
                                                    <i class="fas fa-pencil-alt">
                                                    </i>
                                                    Continuar edición
                                                </a>
                                            </td>
                                        </tr>


                                    @endforeach

                                    </tbody>
                                </table>
                                </div>

                            @endif

                            <h5 class="text-muted mt-2">Archivos adjuntos</h5>
                            <div class="card-body table-responsive p-0">
                                <table class="table text-nowrap table-borderless">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tamaño</th>
                                        <th>Opciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($evidence->proofs as $proof)

                                        <tr>
                                            <td>{{$proof->file->name}}</td>
                                            <td>{{$proof->file->sizeForHuman()}}</td>
                                            <td>
                                                <a class="btn btn-primary btn-sm" href="{{route('proof.download',['instance' => $instance, 'id' => $proof->id])}}">
                                                    <i class="fas fa-download"></i>
                                                    Descargar
                                                </a>
                                            </td>

                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                            </div>


                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection
