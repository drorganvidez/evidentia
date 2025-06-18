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

        <div class="col-lg-8">

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

                            @if($evidence->status == 'DRAFT' and !\Carbon\Carbon::now()->gt(\\Config::upload_evidences_timestamp()))
                                <a class="btn btn-info btn-sm"
                                   href="{{route('evidence.edit',['id' => $evidence->id])}}">
                                    <i class="fas fa-pencil-alt">
                                    </i>
                                </a>
                            @endif

                            @if(!\Carbon\Carbon::now()->gt(\\Config::upload_evidences_timestamp()))
                                <x-buttonconfirm :id="$evidence->id" route="evidence.remove" title="¿Seguro?" description="Esto borrará la evidencia actual, las
                                                ediciones anteriores <b>y todos los archivos adjuntos.</b>" type="REMOVE"/>
                            @endif

                        @endif


                    </div>



                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card">

                <div class="card-body">

                    <h4>Estado</h4>

                    <p class="text-muted">Última edición
                        <b>{{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }}</b>
                    </p>

                    <x-evidencestatus :evidence="$evidence"/>

                    <hr>

                    <h4>Pruebas adjuntas</h4>

                    <div class="row">

                        @foreach($evidence->proofs as $proof)

                            <div class="col-auto mt-3">
                                <a style="margin-bottom: 10px" class="btn btn-default btn-sm" href="{{route('proof.download',['id' => $proof->id])}}">
                                    <i class="fas fa-download"></i>
                                    {{$proof->file->name}} ({{$proof->file->sizeForHuman()}})
                                </a>
                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

    </div>


@endsection
