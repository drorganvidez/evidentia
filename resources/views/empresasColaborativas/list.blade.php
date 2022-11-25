@extends('layouts.app')

@section('title', 'Bienvenid@ a Evidentia')

@section('title-icon', 'fas fa-door-open')

@section('breadcrumb')
    <li class="breadcrumb-item">Home</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-6 col-sm-12">

            <div class="card shadow-lg">

                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge badge-secondary">Empresas Colaborativas Innosoft</span>
                    </h3>

                </div>

                <div class="card-body">
                    <div class="row">
                    @foreach($empresacolaborativa as $e)
                        <div class="col-lg-4">
                            <div class="info-box-content">
                                <span class="info-box-text text-center text-muted">{{$e->name}}</span>
                                <span class="info-box-text text-center text-muted">{{$e->telephone}}</span>
                                <span class="info-box-text text-center text-muted">{{$e->email}}</span>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>

            </div>



        </div>

        <div class="col-lg-6 col-sm-12">

            <div class="callout callout-info">


                <x-evidentiadescription/>
            </div>



        </div>
    </div>


@endsection
