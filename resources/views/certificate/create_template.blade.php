@extends('layouts.app')


@section('title', 'Generar plantilla para diploma')


@section('title-icon', 'fab fa-angellist')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <form method="GET" enctype="multipart/form-data">

        <x-id :id="$evidence->id ?? ''" :edit="$edit ?? ''"/>


        <div class="row">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="form-row">
                            <x-input col="10" attr="title" :value="$evidence->title ?? ''" label="Título del Diploma" description="Escribe un título que describa la plantilla de diploma que vas a generar" required/>
                            <x-textareasimple col="10" attr="html" :value="$evidence->title ?? ''" label="Html del Diploma" description="Contenido de la plantilla en html"/>

                            <div class="form-group col-md-4">
                                <button type="submit" formaction="{{$route}}" class="btn btn-primary btn-block" ><i class="fas fa-external-link-square-alt"></i> &nbsp;Crear Plantilla</button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

    @section('scripts')

    

    @endsection

@endsection


