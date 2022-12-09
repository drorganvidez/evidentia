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

<h5> Secretarios comite 0 {{$secretarios_comite[0]}}</h5>
<h5> Secretarios comite 1 {{$secretarios_comite[1]}}</h5>
<h5> Secretarios comite 2 {{$secretarios_comite[2]}}</h5>
<h5> Secretarios comite 3 {{$secretarios_comite[3]}}</h5>
<h5> Secretarios comite 4 {{$secretarios_comite[4]}}</h5>
<h5> Secretarios comite 5 {{$secretarios_comite[5]}}</h5>
<h5> Secretarios comite 6 {{$secretarios_comite[6]}}</h5>
<h5> Secretarios comite 7 {{$secretarios_comite[7]}}</h5>

@endsection



