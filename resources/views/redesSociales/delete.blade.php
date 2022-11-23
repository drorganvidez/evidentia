@extends('layouts.app')

@section('title', 'Eliminar red social')
@section('title-icon', 'fas fa-trash')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('admin.redesSociales.manage')}}">Gestionar redes sociales</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card shadow-lg">

                <div class="card-body">
                    <div class="callout callout-info">
                        <h5>Estás a punto de eliminar la siguiente red social</h5>

                        <br>

                        <dl class="row">
                            <dt class="col-sm-4">Nombre</dt>
                            <dd class="col-sm-8">{{$redSocial->name}}</dd>
                            <dt class="col-sm-4">Contraseña</dt>
                            <dd class="col-sm-8">{{$redSocial->password}}</dd>
                        </dl>
                    </div>

                    <form method="POST" action="{{ route('admin.redesSociales.manage.remove') }}">
                        @csrf

                        <div class="form-row">

                            <input type="hidden" name="id" value="{{$redSocial->id}}"/>

                        </div>

                        <div class="form-row">
                            <div class="col-12 col-lg-3 col-sm-4">
                                <button type="submit" class="btn btn-danger btn-block">Eliminar Red Social</button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
