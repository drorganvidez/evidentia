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

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">

                            <x-input col="7" attr="reason" :value="$transaccion->reason ?? ''" label="Motivo" description="Escribe un título que describa con precisión tu transacción (Al menos 10 caracteres)"/>

                            <div class="form-group col-md-2">
                                <label for="amount">Cantidad</label>
                                <input id="" type="number"min=0 class="form-control" placeholder="" name="amount" value="{{\Time::complex_shape_minutes($evidence->hours ?? '') }}" autocomplete="amount" autofocus="">
                                
                                @error("minutes")
                                <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label for="type">Tipo</label>
                                <select id="comittee" class="selectpicker form-control @error('comittee') is-invalid @enderror" name="comittee" value="{{ old('comittee') }}" required autofocus>
                                    <option labe='Beneficio' value="Beneficio">Beneficio</option>
                                    <option  value="Gasto">Gasto</option>
                                </select>

                                @error('type')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                           

                            <div class="form-group col-md-4">
                                <button type="button" formaction="{{$route_publish}}" class="btn btn-primary btn-block"><i class="fas fa-external-link-square-alt"></i> &nbsp;Crear transacción</button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

@endsection