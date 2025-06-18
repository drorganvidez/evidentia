@extends('layouts.app')

@section('title', 'Ver evidencia: '.$evidence->title)

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR') and $evidence->committee->id == \Illuminate\Support\Facades\Auth::user()->coordinator->committee->id)
        <li class="breadcrumb-item"><a href="{{route('coordinator.evidence.list.all')}}">Gestionar evidencias de {{\Illuminate\Support\Facades\Auth::user()->coordinator->committee->name}}</a></li>
    @else
        <li class="breadcrumb-item"><a href="{{route('evidence.list')}}">Mis evidencias</a></li>
    @endif

    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-6">

            <div class="card">

                <div class="card-body">

                    <h5>
                        <x-evidencecommittee :evidence="$evidence"/>
                        <span class="badge badge-secondary">
                            <i class="far fa-clock"></i> {{$evidence->hours}} horas
                        </span>
                    </h5>

                    <h4>{{$evidence->title}}</h4>

                    <div class="post text-justify">

                        {!! $evidence->description !!}

                        <br><br>


                        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR') and $evidence->committee->id == \Illuminate\Support\Facades\Auth::user()->coordinator->committee->id)

                            <x-evidencemanagecoordinator :evidence="$evidence" />

                        @else

                            @if($evidence->status == 'DRAFT' and !\Carbon\Carbon::now()->gt(\Config::upload_evidences_timestamp()))
                                <a class="btn btn-info btn-sm"
                                   href="{{route('evidence.edit',['id' => $evidence->id])}}">
                                    <i class="fas fa-pencil-alt">
                                    </i>
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

        <div class="col-lg-6">

        <div class="card">
            <div class="card-body">

                <div class="alert alert-info">
                    <h4 class="mb-2">
                        <i class="fas fa-info-circle"></i> {{ $evidence->status_label}}
                    </h4>
                    <p class="text-muted mb-2">
                        Última edición: <b>{{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }}</b>
                    </p>
                    <x-evidencestatus :evidence="$evidence"/>
                </div>

                <div class="mt-4">

                    @if($evidence->proofs->isEmpty())
                        <p class="text-muted">No hay pruebas adjuntas.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Tamaño</th>
                                        <th class="text-center">Descargar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evidence->proofs as $proof)
                                        <tr>
                                            <td>{{ $proof->file->name }}</td>
                                            <td>{{ $proof->file->sizeForHuman() }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('proof.download', ['id' => $proof->id]) }}"
                                                class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i> Descargar
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>


        </div>

    </div>


@endsection
