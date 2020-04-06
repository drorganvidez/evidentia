@extends('layouts.app')

@section('title', 'Bienvenid@ a Evidentia')

@section('title-icon', 'fas fa-door-open')

@section('breadcrumb')
    <li class="breadcrumb-item">Home</li>
@endsection

@section('content')

    <div class="card">

        <div class="card-body">
            <div class="row">
                <div class="col-lg-8 col-sm-12">
                    <div class="row">

                        @foreach($instances as $instance)
                            <div class="col-lg-6 col-sm-12">
                                <div class="info-box bg-light" onclick="location.href='/{{$instance->route}}';" style="cursor: pointer;">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">{{$instance->name}}</span>
                                        <a href="/{{$instance->route}}">
                                            <span class="info-box-number text-center text-muted mb-0">Acceder</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <x-evidentiadescription/>
                </div>
            </div>
        </div>
    </div>


@endsection
