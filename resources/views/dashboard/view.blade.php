@extends('layouts.app')

@section('title', 'Estadisticas')

@section('title-icon')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

<h2>Estadisticas Generales</h2>
  
<h5> Evidencias totales {{$total_evidences_not_draft}}</h5>

<h5> Usuarios Totales {{$total_users}}</h5>

<h5> Media de evidencias por usuario {{$evidences_per_user}}</h5>

<h5> Archivos totales subidos en evidencias {{$total_files}}</h5>

<h5> Media de archivos subidos por evidencia {{$mean_evidence_proof}}</h5>

<h5> Cuota total de espacio ocupada por archivos subidos {{$total_weight}}</h5>

<h5> Media de cuota de espacio ocupada por archivos en cada evidencia {{$mean_evidences_proof_weight}}</h5>



@endsection



