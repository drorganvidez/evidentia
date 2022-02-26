@extends('layouts.app')

@section('title', 'Actualizaciones')

@section('title-icon', 'fab fa-github')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="error-page">
        <h2 class="headline text-warning"> 403</h2>

        <div class="error-content">
            <h3><i class="fas fa-exclamation-triangle text-warning"></i> Cuota diaria superada</h3>

            <p>
                Por desgracia, la API de GitHub establece una cuota diaria máxima de consulta.
                Inténtalo más tarde o visita <a href="https://github.com/drorganvidez/evidentia"> el proyecto en GitHub</a>
            </p>

        </div>

    </div>

@endsection
