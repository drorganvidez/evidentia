@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar Empresa Colaborativa: '.$empresacolaborativa->name)
@else
    @section('title', 'Crear Empresa Colaborativa')
@endisset

@section('title-icon', 'fas fa-box')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('admin.empresasColaborativas.manage')}}">Gestionar Empresas Colaborativas</a></li>
    @endisset
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card shadow-lg">

                <div class="card-body">
                    <form method="POST" action="{{$route}}">
                        @csrf

                        <x-id :id="$empresacolaborativa->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="name" :value="$empresacolaborativa->name ?? ''" label="Nombre" description="Ejemplo: Mercadona/Dia"/>

                        </div>

                        <div class="form-row">

                            <x-input col="4" attr="telephone" :value="$empresacolaborativa->telephone ?? ''" label="Teléfono" description="Teléfono"/>

                        </div>

                        <div class="form-row">

                            <x-input col="4" attr="email" :value="$empresacolaborativa->email ?? ''" label="Email" description="Email"/>

                        </div>

                        <div class="form-row">
                            <div class="col-12 col-lg-3 col-sm-4">
                                <button type="submit" class="btn btn-primary btn-block">Guardar empresa colaborativa</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
