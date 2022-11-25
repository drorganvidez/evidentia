@extends('layouts.app')

@section('title', 'Eliminar empresa colaborativa')
@section('title-icon', 'fas fa-trash')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('admin.empresasColaborativas.manage')}}">Gestionar empresas colaborativas</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card shadow-lg">

                <div class="card-body">
                    <div class="callout callout-info">
                        <h5>Estás a punto de eliminar la siguiente empresa colaborativa</h5>

                        <br>

                        <dl class="row">
                            <dt class="col-sm-4">Nombre</dt>
                            <dd class="col-sm-8">{{$empresacolaborativa->name}}</dd>
                            <dt class="col-sm-4">Teléfono</dt>
                            <dd class="col-sm-8">{{$empresacolaborativa->telephone}}</dd>
                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{$empresacolaborativa->email}}</dd>
                        </dl>
                    </div>

                    <form method="POST" action="{{ route('admin.empresasColaborativas.manage.remove') }}">
                        @csrf

                        <div class="form-row">

                            <input type="hidden" name="id" value="{{$empresacolaborativa->id}}"/>

                        </div>

                        <div class="form-row">
                            <div class="col-12 col-lg-3 col-sm-4">
                                <button type="submit" class="btn btn-danger btn-block">Eliminar Empresa Colaborativa</button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
