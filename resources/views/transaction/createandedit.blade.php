@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar transacción: '.$evidence->title)
@else
    @section('title', 'Crear transacción')
@endisset

@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
   
    <li class="breadcrumb-item"><a href="{{route('transaction.list',$instance)}}">Transacciones</a></li>
    
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    @isset($edit)

        <div class="collapse" id="collapseExample">

                <div class="row">

                    @foreach($transaction->flow_transaction() as $transaction_i)

                        <div class="col-lg-3 mb-0 pb-0">

                            @if(Request()->route('id') == $transaction_i->id)
                                <div class="small-box bg-info">
                            @else
                                <div class="small-box bg-light">
                            @endif

                                <div class="inner">
                                    <h5>{{ Str::limit($transaction_i->title, 30) }}</h5>

                                    {{ \Carbon\Carbon::parse($transaction_i->created_at)->diffForHumans() }}

                                    ({{$transaction_i->created_at->format('d/m/y H:i:s')}})

                                </div>

                                @if(Request()->route('id') == $transaction_i->id)
                                        <a href="#" class="small-box-footer disabled">Actualmente editando</a>
                                @else
                                    <a href="{{route('transaction.edit',['instance' => $instance, 'id' => $transaction_i->id])}}" class="small-box-footer"><i class="fas fa-pencil-alt"></i> Continuar edición</a>
                                @endif


                            </div>

                        </div>

                    @endforeach

                </div>

        </div>
    @endisset

    <form method="POST" enctype="multipart/form-data">
        @csrf

        <x-id :id="$evidence->id ?? ''" :edit="$edit ?? ''"/>

        <input type="hidden" name="removed_files" id="removed_files"/>


        <div class="row">

            <div class="col-lg-12">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="6" attr="reason" :value="$transaccion->reason ?? ''" label="Motivo" description="Escribe un título que describa con precisión tu transacción (Al menos 10 caracteres)"/>

                            <div class="form-group col-md-2">
                                <label for="amount">Cantidad</label>
                                <input id="" type="number"min=0 class="form-control" placeholder="" name="amount" value="{{\Time::complex_shape_minutes($evidence->hours ?? '') }}" autocomplete="amount" autofocus="">
                                
                                @error("minutes")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label for="type">Tipo</label>
                                <select id="type" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="type" value="Beneficio" required autofocus>
                                    <option label='Beneficio' value="Beneficio">Beneficio</option>
                                    <option  label='Gasto' value="Gasto">Gasto</option>
                                </select>

                                @error('type')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            
                            <div class="form-group col-md-2">
                                <label for="comittee">Comité asociado</label>
                                <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                                    @foreach($comittees as $comittee)
                                        @isset($evidence)
                                            <option {{$comittee->id == old('comittee') || $transaction->comittee->id == $comittee->id ? 'selected' : ''}} value="{{$comittee->id}}">
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
                           

                            <div class="form-group col-md-12">
                                <button type="button"  class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Publicar transacción</button>
                            </div>

                            <div class="modal fade" id="modal-default">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Publicar una transacción</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Cuando se publica una transacción, esta se envía al coordinador de finanzas
                                                para su posterior revisión.
                                            <p>¿Deseas continuar?</p>
                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" formaction="{{$route_publish}}" class="btn btn-primary" data-toggle="modal" data-target="#modal-default"><i class="fas fa-external-link-square-alt"></i> &nbsp;Sí, publicar transacción</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

@endsection