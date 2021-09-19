@extends('layouts.app')

@section('title', 'Gestionar reuniones')

@section('title-icon', 'far fa-handshake')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting/>

        <div class="col-md-9">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Bienvenid@ al gestor de reuniones</h4>

                    <p>Este apartado ha sido rediseñado por completo para dar un mejor servicio a las reuniones de las
                    Jornadas.</p>

                    <p>Por favor, siéntete libre de expresar tu opinión a través del <a href="{{route('suggestionsmailbox',\Instantiation::instance())}}">buzón de sugerencias</a></p>

                </div>

            </div>


        </div>
    </div>


@endsection
