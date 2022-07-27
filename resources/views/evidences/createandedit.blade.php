@extends('layouts.app')

@section('title', 'Evidencias')

@section('submenu')

    <x-submenus.evidences-menu/>

@endsection

@section('content')

    <div class="row">

        <div class="col-lg-6">

            <form>

            <div class="row">

                <x-input>
                    <x-slot:col>
                        col-6 col-md-6
                    </x-slot:col>
                    <x-slot:label>
                        Título
                    </x-slot:label>
                    <x-slot:name>
                        title
                    </x-slot:name>
                    <x-slot:value>
                        {{$evidence->title ?? ''}}
                    </x-slot:value>
                    <x-slot:required></x-slot:required>
                    <x-slot:autofocus></x-slot:autofocus>
                </x-input>

                <x-input>
                    <x-slot:col>
                        col-6 col-md-3
                    </x-slot:col>
                    <x-slot:label>
                        Horas
                    </x-slot:label>
                    <x-slot:name>
                        hours
                    </x-slot:name>
                    <x-slot:value>
                        {{\Time::complex_shape_hours($evidence->hours ?? '')}}
                    </x-slot:value>
                </x-input>

                <x-input>
                    <x-slot:col>
                        col-6 col-md-3
                    </x-slot:col>
                    <x-slot:label>
                        Minutos
                    </x-slot:label>
                    <x-slot:name>
                        minutes
                    </x-slot:name>
                    <x-slot:value>
                        {{\Time::complex_shape_minutes($evidence->hours ?? '')}}
                    </x-slot:value>
                </x-input>

            </div>

            <div class="row">

                <x-select>
                    <x-slot:data>
                        {{$committees}}
                    </x-slot:data>
                    <x-slot:col>
                        col-6 col-md-6
                    </x-slot:col>
                    <x-slot:label>
                        Comité asociado
                    </x-slot:label>
                    <x-slot:option_name>
                        name
                    </x-slot:option_name>
                    <x-slot:name>
                        committee_id
                    </x-slot:name>
                </x-select>

            </div>

            <div class="row">

                <x-textarea>
                    <x-slot:col>
                        col-12 col-md-12
                    </x-slot:col>
                    <x-slot:label>
                        Descripción
                    </x-slot:label>
                    <x-slot:description>
                        Escribe una descripción concisa de tu evidencia (entre 10 y 20000 caracteres).
                    </x-slot:description>
                </x-textarea>


            </div>

            </form>

        </div>

        <div class="col-lg-6">

            <div class="form-group">

                <!-- Label -->
                <label class="form-class mb-3">
                    Subir archivos
                </label>

                <livewire:upload-files evidence_id="{{$evidence_temp->id}}"></livewire:upload-files>

            </div>



            {{--

            <div class="card" data-list='{"valueNames": ["name"]}'>
                <div class="card-header">

                    <!-- Title -->
                    <h4 class="card-header-title">
                        Files
                    </h4>

                    <!-- Select -->
                    <form class="me-3">
                        <select class="form-select form-select-sm form-control-flush" data-choices='{"searchEnabled": false}'>
                            <option value="">Sort order</option>
                            <option value="asc">Asc</option>
                            <option value="desc">Desc</option>
                        </select>
                    </form>

                    <!-- Button -->
                    <a href="#!" class="btn btn-sm btn-primary">
                        Upload
                    </a>

                </div>
                <div class="card-header">

                    <!-- Form -->
                    <form>
                        <div class="input-group input-group-flush input-group-merge input-group-reverse">

                            <!-- Input -->
                            <input class="form-control list-search" type="search" placeholder="Search">

                            <!-- Prepend -->
                            <div class="input-group-text">
                                <span class="fe fe-search"></span>
                            </div>

                        </div>
                    </form>

                </div>
                <div class="card-body">

                    <!-- List -->
                    <ul class="list-group list-group-lg list-group-flush list my-n4">
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                                        <img src="assets/img/files/file-1.jpg" alt="..." class="avatar-img rounded">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">Launchday Logo</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        2.5kb SVG
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                                        <img src="assets/img/files/file-2.jpg" alt="..." class="avatar-img rounded">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">Example Grid</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        1.5mb PNG
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                          <span class="avatar-title rounded bg-white text-secondary">
                            <span class="fe fe-folder"></span>
                          </span>
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">Screenshot Collection</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        6.9mb directory
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                          <span class="avatar-title rounded bg-white text-secondary">
                            <span class="fe fe-package"></span>
                          </span>
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">Database migration (compressed)</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        5.9mb ZIP
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                                        <img src="assets/img/files/file-3.jpg" alt="..." class="avatar-img rounded">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">Launchday Cover</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        750kb JPG
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                          <span class="avatar-title rounded bg-white text-secondary">
                            <span class="fe fe-film"></span>
                          </span>
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">Prototype Screencap</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        23.5mb MOV
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                                        <img src="assets/img/files/file-4.jpg" alt="..." class="avatar-img rounded">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">Header block example</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        1.2mb PNG
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="#!" class="avatar avatar-lg">
                          <span class="avatar-title rounded bg-white text-secondary">
                            <span class="fe fe-pie-chart"></span>
                          </span>
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="#!">User test results slides</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="card-text small text-muted mb-1">
                                        1.1mb PPTX
                                    </p>

                                    <!-- Time -->
                                    <p class="card-text small text-muted">
                                        Uploaded by Dianna Smiley on <time datetime="2018-01-03">Jan 3, 2018</time>
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                        Download
                                    </a>

                                </div>
                                <div class="col-auto">

                                    <!-- Dropdown -->
                                    <div class="dropdown">

                                        <!-- Toggle -->
                                        <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fe fe-more-vertical"></i>
                                        </a>

                                        <!-- Menu -->
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#!" class="dropdown-item">
                                                Action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Another action
                                            </a>
                                            <a href="#!" class="dropdown-item">
                                                Something else here
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                    </ul>

                </div>
            </div>

            --}}

        </div>
    </div>


@endsection
