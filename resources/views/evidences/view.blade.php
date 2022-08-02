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
            <div class="card card-body">

                <div class="row">

                </div>

                <div class="row">
                    <div class="col-8 ">
                        <p class="text-muted mb-0 pb-0">
                            #{{$evidence->id}}
                        </p>

                        <!-- Title -->
                        <h2 class="mb-2 mt-0 pb-0">
                            {{$evidence->title}}
                        </h2>

                        <!-- Text -->
                        <p class="text-muted mb-4">
                            Última actualización:
                            {{ \Carbon\Carbon::parse($evidence->updated_at)->format('d/m/Y H:i')}}
                        </p>

                    </div>

                    <div class="col-4 text-end">

                        <div class="badge bg-{{$evidence->color_status()}} mb-2">
                            {{$evidence->spanish_status()}}
                        </div>

                        <div class="badge bg-primary-soft mb-2">
                            {{$evidence->committee->name}}
                        </div>

                    </div>

                </div>

                @if($evidence->guest != null)
                    <div class="row">

                        <div class="col-6col-md-6">

                            <h6 class="text-muted text-uppercase">
                                Tiempo empleado
                            </h6>

                            <p class="mb-4">
                                {{ $evidence->full_time() }}
                            </p>
                        </div>

                        <div class="col-6 col-md-6">

                            <h6 class="text-muted text-uppercase">
                                Estudiante asociado
                            </h6>

                            <p class="mb-4">
                                {{ $evidence->guest->surname }}, {{ $evidence->guest->name }}
                            </p>
                        </div>

                    </div>

                @endif

                <div class="row">

                    <div class="col-12 col-md-12">

                        <h6 class="text-muted text-uppercase">
                            Descripción
                        </h6>

                        {!! $evidence->description !!}
                    </div>

                </div>

                {{--

                <div class="row">
                    <div class="col-12 col-md-6">

                        <!-- Heading -->
                        <h6 class="text-uppercase text-muted">
                            Invoiced from
                        </h6>

                        <!-- Text -->
                        <p class="text-muted mb-4">
                            <strong class="text-body">Kenny Blankenship</strong> <br>
                            CEO of Good Themes <br>
                            123 Happy Walk Way <br>
                            San Francisco, CA
                        </p>

                        <!-- Heading -->
                        <h6 class="text-uppercase text-muted">
                            Invoiced ID
                        </h6>

                        <!-- Text -->
                        <p class="mb-4">
                            #SDF9823KD
                        </p>

                    </div>
                    <div class="col-12 col-md-6 text-md-end">

                        <!-- Heading -->
                        <h6 class="text-uppercase text-muted">
                            Invoiced to
                        </h6>

                        <!-- Text -->
                        <p class="text-muted mb-4">
                            <strong class="text-body">Jimmy LeBuyer</strong> <br>
                            Acquisitions at Themers <br>
                            236 Main St., #201 <br>
                            Los Angeles, CA
                        </p>

                        <!-- Heading -->
                        <h6 class="text-uppercase text-muted">
                            Due date
                        </h6>

                        <!-- Text -->
                        <p class="mb-4">
                            <time datetime="2018-04-23">Apr 23, 2018</time>
                        </p>

                    </div>
                </div> <!-- / .row -->
                <div class="row">
                    <div class="col-12">

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table my-4">
                                <thead>
                                <tr>
                                    <th class="px-0 bg-transparent border-top-0">
                                        <span class="h6">Description</span>
                                    </th>
                                    <th class="px-0 bg-transparent border-top-0">
                                        <span class="h6">Hours</span>
                                    </th>
                                    <th class="px-0 bg-transparent border-top-0 text-end">
                                        <span class="h6">Cost</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="px-0">
                                        Custom theme development
                                    </td>
                                    <td class="px-0">
                                        125
                                    </td>
                                    <td class="px-0 text-end">
                                        $6,250
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-0">
                                        Logo design
                                    </td>
                                    <td class="px-0">
                                        15
                                    </td>
                                    <td class="px-0 text-end">
                                        $750
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-0 border-top border-top-2">
                                        <strong>Total amount due</strong>
                                    </td>
                                    <td colspan="2" class="px-0 text-end border-top border-top-2">
                            <span class="h3">
                              $7,000
                            </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-5">

                        <!-- Title -->
                        <h6 class="text-uppercase">
                            Notes
                        </h6>

                        <!-- Text -->
                        <p class="text-muted mb-0">
                            We really appreciate your business and if there’s anything else we can do, please let us know! Also, should you need us to add VAT or anything else to this order, it’s super easy since this is a template, so just ask!
                        </p>

                    </div>
                </div> <!-- / .row -->

                --}}
            </div>

        </div>

        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-6">


            <div class="card" @if(count($evidence->proofs) > 0) data-list='{"valueNames": ["name"]}' @endif>

                <div class="card-header">

                    <!-- Title -->
                    <h4 class="card-header-title" id="exampleModalCenterTitle">
                        Archivos adjuntos
                    </h4>

                </div>
                <div class="card-header">

                    <!-- Form -->
                    <form>
                        <div class="input-group input-group-flush input-group-merge input-group-reverse">
                            <input class="form-control list-search" type="search" placeholder="Buscar">
                            <div class="input-group-text">
                                <span class="fe fe-search"></span>
                            </div>
                        </div>
                    </form>

                </div>

                @if(count($evidence->proofs) > 0)

                    <div class="card-body">

                        <!-- List -->
                        <ul class="list-group list-group-lg list-group-flush list my-n4">

                            @foreach($evidence->proofs as $proof)
                                <li class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <a href="#!" style="cursor: default" class="avatar avatar-lg">
                                            <span class="avatar-title rounded bg-white text-secondary">

                                                @php

                                                    $type = $proof->file->type;

                                                    $images = array("png", "jpg", "jpeg", "svg");
                                                    $folders = array("zip", "rar", "tar.gz");
                                                    $docs = array("docs", "docx", "txt" ,"pdf");
                                                    $xls = array("xls", "xlsx");

                                                @endphp

                                                @if(in_array($type, $images))

                                                    <span class="fe fe-image"></span>

                                                @elseif(in_array($type, $folders))

                                                    <span class="fe fe-folder"></span>

                                                @elseif(in_array($type, $docs))

                                                    <span class="fe fe-file-text"></span>

                                                @elseif(in_array($type, $xls))

                                                    <span class="fe fe-pie-chart"></span>

                                                @else

                                                    <span class="fe fe-file"></span>

                                                @endif


                                            </span>
                                            </a>

                                        </div>
                                        <div class="col w-25 ms-n2">

                                            <!-- Title -->
                                            <h4 class="mb-1 name">
                                                <a href="{{route('download.file', ['instance' => \Instantiation::instance(), 'file_id' => $proof->file->id])}}">

                                                    {{$proof->file->name}}

                                                </a>
                                            </h4>

                                            <!-- Size -->
                                            <p class="card-text small text-muted mb-1">
                                                {{$proof->file->sizeForHuman()}}
                                            </p>

                                        </div>
                                        <div class="col-auto text-end d-none d-lg-block ">

                                            <!-- Button -->
                                            <a href="{{route('download.file', ['instance' => \Instantiation::instance(), 'file_id' => $proof->file->id])}}" class="btn btn-sm btn-white d-md-inline-block">
                                                <i class="fe fe-download"></i> Descargar
                                            </a>

                                        </div>

                                    </div> <!-- / .row -->
                                </li>
                            @endforeach

                        </ul>

                    </div>
                @else

                    <div class="card-body">
                        Sin archivos
                    </div>

                @endif

            </div>


        </div>

    </div> <!-- / .row -->

    <div class="row">



    </div>

@endsection
