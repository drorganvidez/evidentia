@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar Red Social: '.$redsocial->name)
@else
    @section('title', 'Crear Red Social')
@endisset

@section('title-icon', 'fas fa-box')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('admin.redesSociales.manage')}}">Gestionar Redes Sociales</a></li>
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

                        <x-id :id="$redsocial->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="name" :value="$redsocial->name ?? ''" label="Nombre" description="Ejemplo: Twitter/Facebook"/>

                        </div>

                        <div class="form-row">

                            <x-input col="4" attr="password" :value="$redsocial->password ?? ''" label="Contraseña" description="Contraseña"/>

                        </div>

                        <div class="form-row">
                            <div class="col-12 col-lg-3 col-sm-4">
                                <button type="submit" class="btn btn-primary btn-block">Guardar red social</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

@endsection
