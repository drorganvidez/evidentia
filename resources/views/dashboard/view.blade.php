@extends('layouts.app')

@section('title', 'Estadisticas')

@section('title-icon')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

<h3>Estadisticas Generales</h3>
  
<h5> Evidencias totales: {{$total_evidences_not_draft}}</h5>

<h5> Usuarios Totales: {{$total_users}}</h5>

<h5> Media de evidencias por usuario: {{$evidences_per_user}}</h5>

<h5> Archivos totales subidos en evidencias: {{$total_files}}</h5>

<h5> Media de archivos subidos por evidencia: {{$mean_evidence_proof}}</h5>

<h5> Cuota total de espacio ocupada por archivos subidos: {{$total_weight}}</h5>

@for($i =0; $i < 4; $i++)

<h5> Evidencias entre {{$i}} horas y {{$i+1}} horas: {{$dict_evidences_hours[$i]}}</h5>

@endfor

<h5> Media de cuota de espacio ocupada por archivos en cada evidencia {{$mean_evidences_proof_weight}}</h5>

@for($i =0; $i < 4; $i++)

<h5> Evidencias que contienen {{$i}} archivos: {{$evidences_proof_range[$i]}}</h5>

@endfor

<h5> Meetings totales: {{$total_meetings}}</h5>

<h5> Tiempo total invertido en meetings: {{$total_time_meetings}} horas</h5>

<h5> Tiempo medio invertido en cada meeting: {{$meen_time_meetings}} horas</h5>

<br></br>

@for($i =0; $i < 8; $i++)

<br></br>

<h3>Comite de {{$comite_name[$i]}}</h3>

<h5>Secretarios en el comite {{$secretarios_comite[$i]}} </h5>

<h5>Evidencias del comite {{$evidences_per_comite[$i]}} </h5>

<h5>Cuota de almacenaje usada por el comite {{$comite_weight[$i]}} </h5>

<h5>Meetings realizados en el comite: {{$meetings_comite[$i]}} </h5>


@endfor

@endsection



