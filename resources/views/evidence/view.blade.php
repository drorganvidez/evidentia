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

        <div class="col-lg-4">

            <div class="row">

                <div class="col-lg-12">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <label for="title">Archivos adjuntos</label>

                            <br>

                            @foreach($evidence->proofs as $proof)

                                <a class="btn btn-primary btn-sm" href="{{route('proof.download',['instance' => $instance, 'id' => $proof->id])}}">
                                    <i class="fas fa-download"></i>
                                    {{$proof->file->name}} ({{$proof->file->sizeForHuman()}})
                                </a>

                            @endforeach

                        </div>

                    </div>

                </div>

                <div class="col-lg-12">

                    <div class="card shadow-sm">

                        <div class="card-body">
                            <div class="text-muted">
                                <p class="text-muted">Última edición
                                    <b>{{ \Carbon\Carbon::parse($evidence->created_at)->diffForHumans() }}</b>
                                </p>
                            </div>

                            <x-evidencestatus :evidence="$evidence"/>

                            <br>



                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>


@endsection
