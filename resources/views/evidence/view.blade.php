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

        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">

                    <h5>
                        <x-evidencecomittee :evidence="$evidence"/>
                        <span class="badge badge-secondary">
                            <i class="far fa-clock"></i> {{$evidence->hours}} horas
                        </span>
                    </h5>

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

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Estado</h4>

                    <p class="text-muted">Última edición
                        <b>{{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }}</b>
                    </p>

                    <x-evidencestatus :evidence="$evidence"/>

                    <hr>

                    <h4>Pruebas adjuntas</h4>

                    <div class="col">

                        @foreach($dict_storaged_files as $fileType)
                            <div class="row mt-3">
                                <h5 class='mx-3'>{{Str::upper($fileType)}}</h5>
                            </div>
                            @foreach($evidence->proofs as $proof)
                                @if($proof->file->type == $fileType)
                                    <div class="row mt-1 mx-2">
                                        <a style="margin-bottom: 10px" class="btn btn-default btn-sm" href="{{route('proof.download',['instance' => $instance, 'id' => $proof->id])}}">
                                            <i class="fas fa-download"></i>
                                            {{$proof->file->name}} ({{$proof->file->sizeForHuman()}})
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        @endforeach

                    </div>

                    <h4>Pruebas verificadas</h4>
                    <div class="col">
                        @foreach($dict_vp_filetypes as $fileType)
                            <div class="row mt-3">
                                <h5 class='mx-3'>{{Str::upper($fileType)}}</h5>
                            </div>
                            @foreach ($evidence->verified_proofs as $proof)
                                <div class="row mt-1 mx-2">
                                    <p style="margin-bottom: 10px" class="btn btn-default btn-sm disabled">
                                        <i class="fas fa-file"></i>
                                        {{$proof->name}} ({{$proof->size}})
                                    </p>
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                </div>

            </div>

        </div>

    </div>


@endsection
