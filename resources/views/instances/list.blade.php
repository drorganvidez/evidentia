@extends('layouts.app')

@section('title', 'Bienvenid@ a Evidentia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

        <div class="row">

            @foreach($instances as $instance)

                <div class="col-lg-4 col-4">
                    <div class="small-box bg-light" onclick="location.href='/{{$instance->route}}';" style="cursor: pointer;">
                        <div class="inner">
                            <h3>{{$instance->name}}</h3>

                            <p>&nbsp;</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <a href="/{{$instance->route}}" class="small-box-footer">Acceder <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

            @endforeach

        </div>

@endsection
