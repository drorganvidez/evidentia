@extends('layouts.app')

@section('title', 'Ajustes de Eventbrite')

@section('title-icon', 'fas fa-screwdriver')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">

                    <form action="{{$route}}" method="POST">
                        @csrf

                        <div class="row">

                            <div class="col-lg-6">

                                <i>Powered by</i>
                                <br>
                                <img src="{{asset('dist/img/eventbrite.svg')}}" width="200px"/>

                                <br>
                                <br>
                                <p>
                                    Es posible centralizar los eventos y las asistencias registradas en Eventbrite. Para
                                    ello, debes obtener un token y así poder usar la API.
                                </p>

                                <p>
                                    <a href="https://www.eventbrite.com/platform/api-keys" target="_blank">Obtener el token.</a>
                                </p>

                            </div>

                        </div>

                        <div class="form-row">

                            <x-input col="6" attr="token" :value="$token" label="Token" description="Introduce el token proporcionado por Eventbrite"/>

                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <button type="submit" class="btn btn-primary">Validar y guardar token</button>
                            </div>
                        </div>

                    </form>


                </div>

            </div>


        </div>
    </div>

@endsection
