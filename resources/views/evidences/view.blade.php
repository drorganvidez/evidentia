@extends('layouts.app')

@section('title', 'Viendo: ' . $evidence->title)

@section('options')
    <!-- Buttons -->
    <a href="{{route('evidences.export', ['instance' => \Instantiation::instance(), 'id' => $evidence->id])}}" class="btn btn-primary ms-2 lift">
        <i class="fe fe-download-cloud"></i> Exportar
    </a>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-6">
            <a href="{{url()->previous() }}" class="btn btn-outline-primary mb-4">
                <i class="fe fe-skip-back"></i> Volver
            </a>
        </div>

    </div>

    <div class="row">

        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">

            <!-- Content -->
            <x-view-evidence :evidence="$evidence"></x-view-evidence>

        </div>

        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">


            <x-view-files :evidence="$evidence"></x-view-files>


        </div>

    </div> <!-- / .row -->

    <div class="row">



    </div>

@endsection
