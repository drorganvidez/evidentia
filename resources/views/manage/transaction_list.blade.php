@extends('layouts.app')

@section('title', 'Gestionar transacciones')

@section('title-icon', 'nav-icon fas fa-clipboard-check')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="row mb-3">
                <p style="padding: 5px 25px 0px 15px">Exportar tabla:</p>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('transaction.export',['instance' => $instance, 'type' => 'all', 'ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('transaction.export',['instance' => $instance,'type' => 'all', 'ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('transaction.export',['instance' => $instance, 'type' => 'all', 'ext' => 'pdf'])}}"
                       class="btn btn-info btn-block" role="button">
                        PDF</a>
                </div>
            </div>

            <div class="card shadow-lg">

<div class="card-body">
    <div class="table-responsive">
        <table id="dataset" class="table table-hover table-responsive">
        <thead>
        <tr>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">ID</th>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Motivo</th>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Tipo</th>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Cantidad</th>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Fecha</th>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Estado</th>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Comité</th>
            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Acciones</th>
        </tr>
        </thead>
        <tbody>

        @foreach($transactions as $transaction)
            <tr>
                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$transaction->id}}</td>
                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$transaction->reason}}</td>
                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$transaction->type}}</td>
                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$transaction->amount}}</td>
                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$transaction->created_at}}</td>
                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><x-transactioncomittee :transaction="$transaction"/></td>
                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                        @if($transaction->status == "PENDING")

                            <div class="progress progress-sm">
                                <div class="progress-bar bg-gradient-gray-dark" data-toggle="tooltip" data-placement="right" title="Pendiente de revisión" role="progressbar" aria-volumenow="66" aria-volumemin="0" aria-volumemax="100" style="width: 66%">
                                </div>
                            </div>

                        @endif

                        @if($transaction->status == "ACCEPTED")
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-gradient-success" data-toggle="tooltip" data-placement="right" title="Aceptada" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
                                </div>
                            </div>
                        @endif

                        @if($transaction->status == "REJECTED")
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-gradient-danger" role="progressbar" data-toggle="tooltip" data-placement="right" title="Rechazada" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
                                </div>
                            </div>
                        @endif
                </td>   
                        @if($transaction->status == 'PENDING')
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                <a href="{{route('president.transaction.accept',['instance' => \Instantiation::instance() , 'id' => $transaction->id])}}" class="btn btn-info btn-block" role="button"> Aceptar</a>
                                <a href="{{route('president.transaction.reject',['instance' => \Instantiation::instance() , 'id' => $transaction->id])}}" class="btn btn-info btn-block" role="button"> Denegar</a>
                            </td>
                        @endif
                        @if($transaction->status != 'PENDING')
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                <a href="{{route('president.transaction.accept',['instance' => \Instantiation::instance() , 'id' => $transaction->id])}}" class="btn btn-info btn-block disabled" role="button"> Aceptar</a>
                                <a href="{{route('president.transaction.reject',['instance' => \Instantiation::instance() , 'id' => $transaction->id])}}" class="btn btn-info btn-block disabled" role="button"> Denegar</a>
                            </td>
                        @endif
            </tr>
                        @endforeach
                        <tr>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>Total:</b></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>{{$total}}€</b></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>Total Aceptado:</b></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>{{$aceptado}}€</b></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>Total No Aceptado:</b></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>{{$noAceptado}}€</b></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>Total Pendiente:</b></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><b>{{$pendiente}}€</b></td>
                        </tr>

        </tbody>
    </table>
    </div>
</div>
</div>

        </div>
    </div>

@endsection