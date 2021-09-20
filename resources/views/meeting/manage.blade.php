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

                    <p>Por favor, siéntete libre de expresar tu opinión a través del
                        <a href="{{route('suggestionsmailbox',\Instantiation::instance())}}">buzón de sugerencias</a></p>

                    <hr>

                    <h5>
                        Pasos para gestionar correctamente las reuniones:
                    </h5>

                    <dl>
                        <dt>1. Crear convocatoria de reunión</dt>
                        <dd>Una convocatoria de reunión te permite definir los puntos del orden del día que vais a tratar en la reunión.</dd>
                        <dt>2. Crear hoja de firmas</dt>
                        <dd>Los asistentes a la reunión pueden acceder a una URL generada por el sistema con el que firmarán su asistencia
                        con su usuario y contraseña.</dd>
                        <dt>3. Crear acta de reunión</dt>
                        <dd>Puedes crear el acta de reunión usando la convocatoria y la hoja de firmas: tanto los puntos del día como las firmas se
                        incorporan automáticamente al acta.</dd>
                    </dl>

                    <ol>
                        <li>Crear convocatoria de reunión</li>
                    </ol>

                </div>

            </div>


        </div>
    </div>


@endsection
