@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar evidencia: '.$evidence->title)
@else
    @section('title', 'Crear evidencia')
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
                    <i class="fas fa-eraser"></i> Ver todas las ediciones
                </button>
            </p>
        </div>
    </div>

    <div class="collapse" id="collapseExample">

            <div class="row">

                @foreach($evidence->flow_evidences() as $evidence_i)

                    <div class="col-lg-3 mb-0 pb-0">

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
    @endisset

@endsection