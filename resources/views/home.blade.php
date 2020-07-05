@extends('layouts.app')

@section('title', 'Bienvenid@')
@section('title-icon', 'fas fa-home')

@section('content')

    <div class="row">
        <div class="col-lg-6 col-sm-12">

            <div class="row">

                <div class="col-lg-6">

                    <x-infomeetingcount :user="Auth::user()" />


                </div>

                <div class="col-lg-6">

                    <x-infomeetinghours :user="Auth::user()" />

                </div>

            </div>

        </div>


    </div>


@endsection
