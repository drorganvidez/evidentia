@extends('layouts.app')

@section('title', 'Ver incidencia: '.$incidence->title)

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR') and $incidence->comittee->id == \Illuminate\Support\Facades\Auth::user()->coordinator->comittee->id)
        <li class="breadcrumb-item"><a href="{{route('coordinator.incidence.list.all',$instance)}}">Gestionar incidencias de {{\Illuminate\Support\Facades\Auth::user()->coordinator->comittee->name}}</a></li>
    @else
        <li class="breadcrumb-item"><a href="{{route('incidence.list',$instance)}}">Mis incidencias</a></li>
    @endif
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">


                    <h4>{{$incidence->title}}</h4>

                    <div class="post text-justify">

                        {!! $incidence->description !!}

                        <br><br>


                        @if(\Illuminate\Support\Facades\Auth::user()->hasRole('COORDINATOR') and $incidence->comittee->id == \Illuminate\Support\Facades\Auth::user()->coordinator->comittee->id)


                        @else

                            @if($incidence->status != 'IN REVIEW' and !\Carbon\Carbon::now()->gt(\Config::upload_incidences_timestamp()))
                                <x-buttonconfirm :id="$incidence->id" route="incidence.remove" title="¿Seguro?" description="Esto borrará la incidencia actual, las
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
                        <b>{{ \Carbon\Carbon::parse($incidence->created_at)->diffForHumans() }}</b>
                    </p>

                    <x-incidencestatus :incidence="$incidence"/>

                    <hr

                    <h4>Pruebas adjuntas</h4>

                    <div class="row">
                    @foreach($incidence->proofs as $proof)
                        <div class="col-auto mt-3">
                            <a style="margin-bottom: 10px" class="btn btn-default btn-sm" href="{{route('incidence.proof.download',['instance' => $instance, 'id' => $proof->id])}}">
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
