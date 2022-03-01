<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Software libre para la gestión de evidencias de trabajo en jornadas docentes" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>

    <!-- Base CSS -->
    <link rel="stylesheet" href="{{asset('css/app.css')}}" />

    <!-- Map CSS -->
    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.css" />

    <!-- Libs CSS -->
    <link rel="stylesheet" href="{{ asset('css/libs.bundle.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/theme.bundle.css') }}" />

    <!-- Title -->
    <title>@yield('title') @yield('parent') | Evidentia Cloud</title>
</head>
<body>

<!-- MODALS -->
<!-- Modal: Members -->
<div class="modal fade" id="modalMembers" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-card card" data-list='{"valueNames": ["name"]}'>
                <div class="card-header">

                    <!-- Title -->
                    <h4 class="card-header-title" id="exampleModalCenterTitle">
                        Add a member
                    </h4>

                    <!-- Close -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

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
                <div class="card-body">

                    <!-- List group -->
                    <ul class="list-group list-group-flush list my-n3">
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="./profile-posts.html" class="avatar">
                                        <img src="./assets/img/avatars/profiles/avatar-5.jpg" alt="..." class="avatar-img rounded-circle">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="./profile-posts.html">Miyah Myles</a>
                                    </h4>

                                    <!-- Time -->
                                    <p class="small mb-0">
                                        <span class="text-success">●</span> Online
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white">
                                        Add
                                    </a>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="./profile-posts.html" class="avatar">
                                        <img src="./assets/img/avatars/profiles/avatar-6.jpg" alt="..." class="avatar-img rounded-circle">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="./profile-posts.html">Ryu Duke</a>
                                    </h4>

                                    <!-- Time -->
                                    <p class="small mb-0">
                                        <span class="text-success">●</span> Online
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white">
                                        Add
                                    </a>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="./profile-posts.html" class="avatar">
                                        <img src="./assets/img/avatars/profiles/avatar-7.jpg" alt="..." class="avatar-img rounded-circle">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="./profile-posts.html">Glen Rouse</a>
                                    </h4>

                                    <!-- Time -->
                                    <p class="small mb-0">
                                        <span class="text-warning">●</span> Busy
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white">
                                        Add
                                    </a>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                        <li class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="./profile-posts.html" class="avatar">
                                        <img src="./assets/img/avatars/profiles/avatar-8.jpg" alt="..." class="avatar-img rounded-circle">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1 name">
                                        <a href="./profile-posts.html">Grace Gross</a>
                                    </h4>

                                    <!-- Time -->
                                    <p class="small mb-0">
                                        <span class="text-danger">●</span> Offline
                                    </p>

                                </div>
                                <div class="col-auto">

                                    <!-- Button -->
                                    <a href="#!" class="btn btn-sm btn-white">
                                        Add
                                    </a>

                                </div>
                            </div> <!-- / .row -->
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Kanban task -->
<div class="modal fade" id="modalKanbanTask" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-lighter">
            <div class="modal-body">

                <!-- Header -->
                <div class="row">
                    <div class="col">

                        <!-- Prettitle -->
                        <h6 class="text-uppercase text-muted mb-3">
                            <a href="#!" class="text-reset">How to Use Kanban</a>
                        </h6>

                        <!-- Title -->
                        <h2 class="mb-2">
                            Update Dashkit to include new components!
                        </h2>

                        <!-- Subtitle -->
                        <p class="text-muted mb-0">
                            This is a description of this task. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum magna nisi, ultrices ut pharetra eget.
                        </p>

                    </div>
                    <div class="col-auto">

                        <!-- Close -->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>
                </div> <!-- / .row -->

                <!-- Divider -->
                <hr class="my-4">

                <!-- Buttons -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col">

                            <!-- Reaction -->
                            <a href="#!" class="btn btn-sm btn-white">
                                😬 1
                            </a>
                            <a href="#!" class="btn btn-sm btn-white">
                                👍 2
                            </a>
                            <a href="#!" class="btn btn-sm btn-white">
                                Add Reaction
                            </a>

                        </div>
                        <div class="col-auto me-n3">

                            <!-- Avatar group -->
                            <div class="avatar-group d-none d-sm-flex">
                                <a href="./profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="Ab Hadley">
                                    <img src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." class="avatar-img rounded-circle">
                                </a>
                                <a href="./profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="Adolfo Hess">
                                    <img src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." class="avatar-img rounded-circle">
                                </a>
                                <a href="./profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="Daniela Dewitt">
                                    <img src="./assets/img/avatars/profiles/avatar-4.jpg" alt="..." class="avatar-img rounded-circle">
                                </a>
                                <a href="./profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="Miyah Myles">
                                    <img src="./assets/img/avatars/profiles/avatar-5.jpg" alt="..." class="avatar-img rounded-circle">
                                </a>
                            </div>

                        </div>
                        <div class="col-auto">

                            <!-- Button -->
                            <a href="#!" class="btn btn-sm btn-white">
                                Share
                            </a>

                        </div>
                    </div> <!-- / .row -->
                </div>

                <!-- Card -->
                <div class="card">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title">
                            Files
                        </h4>

                        <!-- Button -->
                        <a href="#!" class="btn btn-sm btn-white">
                            Add files
                        </a>

                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush my-n3">
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">

                                        <!-- Avatar -->
                                        <a href="./project-overview.html" class="avatar">
                                            <img src="./assets/img/files/file-1.jpg" alt="..." class="avatar-img rounded">
                                        </a>

                                    </div>
                                    <div class="col ms-n2">

                                        <!-- Title -->
                                        <h4 class="mb-1">
                                            <a href="./project-overview.html">Launchday logo</a>
                                        </h4>

                                        <!-- Time -->
                                        <p class="card-text small text-muted">
                                            1.5mb PNG Dave
                                        </p>

                                    </div>
                                    <div class="col-auto">

                                        <!-- Dropdown -->
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
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
                            </div>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">

                                        <!-- Avatar -->
                                        <a href="./project-overview.html" class="avatar">
                                            <img src="./assets/img/files/file-1.jpg" alt="..." class="avatar-img rounded">
                                        </a>

                                    </div>
                                    <div class="col ms-n2">

                                        <!-- Title -->
                                        <h4 class="mb-1">
                                            <a href="./project-overview.html">Launchday logo</a>
                                        </h4>

                                        <!-- Time -->
                                        <p class="card-text small text-muted">
                                            1.5mb PNG Dave
                                        </p>

                                    </div>
                                    <div class="col-auto">

                                        <!-- Dropdown -->
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
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
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Form -->
                                <form class="mt-1">
                                    <textarea class="form-control form-control-flush form-control" data-autosize rows="1" placeholder="Leave a comment"></textarea>
                                </form>

                            </div>
                            <div class="col-auto align-self-end">

                                <!-- Icons -->
                                <div class="text-muted mb-2">
                                    <a href="#!" class="text-reset me-3">
                                        <i class="fe fe-camera"></i>
                                    </a>
                                    <a href="#!" class="text-reset me-3">
                                        <i class="fe fe-paperclip"></i>
                                    </a>
                                    <a href="#!" class="text-reset">
                                        <i class="fe fe-mic"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        <!-- Comments -->
                        <div class="comment mb-3">
                            <div class="row">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a class="avatar avatar-sm" href="./profile-posts.html">
                                        <img src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." class="avatar-img rounded-circle">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Body -->
                                    <div class="comment-body">

                                        <div class="row">
                                            <div class="col">

                                                <!-- Title -->
                                                <h5 class="comment-title">
                                                    Ab Hadley
                                                </h5>

                                            </div>
                                            <div class="col-auto">

                                                <!-- Time -->
                                                <time class="comment-time">
                                                    11:12
                                                </time>

                                            </div>
                                        </div> <!-- / .row -->

                                        <!-- Text -->
                                        <p class="comment-text">
                                            Looking good Dianna! I like the image grid on the left, but it feels like a lot to process and doesn't really <em>show</em> me what the product does? I think using a short looping video or something similar demo'ing the product might be better?
                                        </p>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </div>
                        <div class="comment">
                            <div class="row">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a class="avatar avatar-sm" href="./profile-posts.html">
                                        <img src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." class="avatar-img rounded-circle">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Body -->
                                    <div class="comment-body">

                                        <div class="row">
                                            <div class="col">

                                                <!-- Title -->
                                                <h5 class="comment-title">
                                                    Adolfo Hess
                                                </h5>

                                            </div>
                                            <div class="col-auto">

                                                <!-- Time -->
                                                <time class="comment-time">
                                                    11:12
                                                </time>

                                            </div>
                                        </div> <!-- / .row -->

                                        <!-- Text -->
                                        <p class="comment-text">
                                            Any chance you're going to link the grid up to a public gallery of sites built with Launchday?
                                        </p>

                                    </div>

                                </div>
                            </div> <!-- / .row -->
                        </div>

                    </div>
                </div>

                <!-- Card -->
                <div class="card mb-0">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title">
                            Activity
                        </h4>

                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush list-group-activity my-n3">
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-auto">

                                        <!-- Avatar -->
                                        <div class="avatar avatar-sm">
                                            <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                                        </div>

                                    </div>
                                    <div class="col ms-n2">

                                        <!-- Heading -->
                                        <h5 class="mb-1">
                                            Johnathan Goldstein
                                        </h5>

                                        <!-- Text -->
                                        <p class="small text-gray-700 mb-0">
                                            Uploaded the files “Launchday Logo” and “Revisiting the Past”.
                                        </p>

                                        <!-- Time -->
                                        <small class="text-muted">
                                            2m ago
                                        </small>

                                    </div>
                                </div> <!-- / .row -->
                            </div>
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-auto">

                                        <!-- Avatar -->
                                        <div class="avatar avatar-sm">
                                            <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                                        </div>

                                    </div>
                                    <div class="col ms-n2">

                                        <!-- Heading -->
                                        <h5 class="mb-1">
                                            Johnathan Goldstein
                                        </h5>

                                        <!-- Text -->
                                        <p class="small text-gray-700 mb-0">
                                            Uploaded the files “Launchday Logo” and “Revisiting the Past”.
                                        </p>

                                        <!-- Time -->
                                        <small class="text-muted">
                                            2m ago
                                        </small>

                                    </div>
                                </div> <!-- / .row -->
                            </div>
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-auto">

                                        <!-- Avatar -->
                                        <div class="avatar avatar-sm">
                                            <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                                        </div>

                                    </div>
                                    <div class="col ms-n2">

                                        <!-- Heading -->
                                        <h5 class="mb-1">
                                            Johnathan Goldstein
                                        </h5>

                                        <!-- Text -->
                                        <p class="small text-gray-700 mb-0">
                                            Uploaded the files “Launchday Logo” and “Revisiting the Past”.
                                        </p>

                                        <!-- Time -->
                                        <small class="text-muted">
                                            2m ago
                                        </small>

                                    </div>
                                </div> <!-- / .row -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: Kanban task empty -->
<div class="modal fade" id="modalKanbanTaskEmpty" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-lighter">
            <div class="modal-body">

                <!-- Header -->
                <div class="row">
                    <div class="col">

                        <!-- Prettitle -->
                        <h6 class="text-uppercase text-muted mb-3">
                            <a href="#!" class="text-reset">How to Use Kanban</a>
                        </h6>

                        <!-- Title -->
                        <h2 class="mb-2">
                            Update Dashkit to include new components!
                        </h2>

                        <!-- Subtitle -->
                        <textarea class="form-control form-control-flush form-control-auto" data-autosize rows="1" placeholder="Add a description..."></textarea>

                    </div>
                    <div class="col-auto">

                        <!-- Close -->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>
                </div> <!-- / .row -->

                <!-- Divider -->
                <hr class="my-4">

                <!-- Buttons -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col">

                            <!-- Button -->
                            <a href="#!" class="btn btn-sm btn-white">
                                Add Reaction
                            </a>

                        </div>
                        <div class="col-auto">

                            <!-- Button -->
                            <a href="#!" class="btn btn-sm btn-white">
                                Share
                            </a>

                        </div>
                    </div> <!-- / .row -->
                </div>

                <!-- Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="dropzone dropzone-multiple" data-dropzone='{"url": "https://"}'>

                            <!-- Fallback -->
                            <div class="fallback">
                                <div class="form-group">
                                    <label class="form-label" for="customFileUpload">Choose file</label>
                                    <input class="form-control" type="file" id="customFileUpload" multiple>
                                </div>
                            </div>

                            <!-- Preview -->
                            <ul class="dz-preview dz-preview-multiple list-group list-group-lg list-group-flush">
                                <li class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Image -->
                                            <div class="avatar">
                                                <img class="avatar-img rounded" src="data:image/svg+xml,%3csvg3c/svg%3e" alt="..." data-dz-thumbnail>
                                            </div>

                                        </div>
                                        <div class="col ms-n3">

                                            <!-- Heading -->
                                            <h4 class="mb-1" data-dz-name>...</h4>

                                            <!-- Text -->
                                            <small class="text-muted" data-dz-size></small>

                                        </div>
                                        <div class="col-auto">

                                            <!-- Dropdown -->
                                            <div class="dropdown">
                                                <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-more-vertical"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="#" class="dropdown-item" data-dz-remove>
                                                        Remove
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>

                <!-- Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="...">
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Form -->
                                <form class="mt-1">
                                    <textarea class="form-control form-control-flush" data-autosize rows="1" placeholder="Leave a comment"></textarea>
                                </form>

                            </div>
                            <div class="col-auto align-self-end">

                                <!-- Icons -->
                                <div class="text-muted mb-2">
                                    <a href="#!" class="text-reset me-3">
                                        <i class="fe fe-camera"></i>
                                    </a>
                                    <a href="#!" class="text-reset me-3">
                                        <i class="fe fe-paperclip"></i>
                                    </a>
                                    <a href="#!" class="text-reset">
                                        <i class="fe fe-mic"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- OFFCANVAS -->

<!-- Offcanvas: Search -->
<div class="offcanvas offcanvas-start" id="sidebarOffcanvasSearch" tabindex="-1">
    <div class="offcanvas-body" data-list='{"valueNames": ["name"]}'>

        <!-- Form -->
        <form class="mb-4">
            <div class="input-group input-group-merge input-group-rounded input-group-reverse">
                <input class="form-control list-search" type="search" placeholder="Buscar">
                <div class="input-group-text">
                    <span class="fe fe-search"></span>
                </div>
            </div>
        </form>

        <!-- List group -->
        <div class="my-n3">
            <div class="list-group list-group-flush list-group-focus list">
                <a class="list-group-item" href="./team-overview.html">
                    <div class="row align-items-center">
                        <div class="col-auto">

                            <!-- Avatar -->
                            <div class="avatar">
                                <img src="./assets/img/avatars/teams/team-logo-1.jpg" alt="..." class="avatar-img rounded">
                            </div>

                        </div>
                        <div class="col ms-n2">

                            <!-- Title -->
                            <h4 class="text-body text-focus mb-1 name">
                                Texto lateral
                            </h4>

                            <!-- Time -->
                            <p class="small text-muted mb-0">
                                <span class="fe fe-clock"></span> <time datetime="2018-05-24">Updated 2hr ago</time>
                            </p>

                        </div>
                    </div> <!-- / .row -->
                </a>
            </div>
        </div>

    </div>
</div>

<!-- Offcanvas: Activity -->
<div class="offcanvas offcanvas-start" id="sidebarOffcanvasActivity" tabindex="-1">
    <div class="offcanvas-header">

        <!-- Title -->
        <h4 class="offcanvas-title">
            Notifications
        </h4>

        <!-- Navs -->
        <ul class="nav nav-tabs nav-tabs-sm modal-header-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#modalActivityAction">Action</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#modalActivityUser">User</a>
            </li>
        </ul>

    </div>
    <div class="offcanvas-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="modalActivityAction">

                <!-- List group -->
                <div class="list-group list-group-flush list-group-activity my-n3">
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-mail"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Launchday 1.4.0 update email sent
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Sent to all 1,851 subscribers over a 24 hour period
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-archive"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    New project "Goodkit" created
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Looks like there might be a new theme soon.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-code"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Dashkit 1.5.0 was deployed.
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    A successful to deploy to production was executed.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-git-branch"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    "Update Dependencies" branch was created.
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    This branch was created off of the "master" branch.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-mail"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Launchday 1.4.0 update email sent
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Sent to all 1,851 subscribers over a 24 hour period
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-archive"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    New project "Goodkit" created
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Looks like there might be a new theme soon.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-code"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Dashkit 1.5.0 was deployed.
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    A successful to deploy to production was executed.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-git-branch"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    "Update Dependencies" branch was created.
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    This branch was created off of the "master" branch.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-mail"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Launchday 1.4.0 update email sent
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Sent to all 1,851 subscribers over a 24 hour period
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-archive"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    New project "Goodkit" created
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Looks like there might be a new theme soon.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-code"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Dashkit 1.5.0 was deployed.
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    A successful to deploy to production was executed.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-primary-soft rounded-circle text-primary">
                                        <i class="fe fe-git-branch"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    "Update Dependencies" branch was created.
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    This branch was created off of the "master" branch.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                </div>

            </div>
            <div class="tab-pane fade" id="modalActivityUser">

                <!-- List group -->
                <div class="list-group list-group-flush list-group-activity my-n3">
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Dianna Smiley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Uploaded the files "Launchday Logo" and "New Design".
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Ab Hadley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Shared the "Why Dashkit?" post with 124 subscribers.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    1h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-offline">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Adolfo Hess
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Exported sales data from Launchday's subscriber data.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    3h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Dianna Smiley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Uploaded the files "Launchday Logo" and "New Design".
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Ab Hadley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Shared the "Why Dashkit?" post with 124 subscribers.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    1h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-offline">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Adolfo Hess
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Exported sales data from Launchday's subscriber data.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    3h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Dianna Smiley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Uploaded the files "Launchday Logo" and "New Design".
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Ab Hadley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Shared the "Why Dashkit?" post with 124 subscribers.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    1h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-offline">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Adolfo Hess
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Exported sales data from Launchday's subscriber data.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    3h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Dianna Smiley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Uploaded the files "Launchday Logo" and "New Design".
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    2m ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-online">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Ab Hadley
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Shared the "Why Dashkit?" post with 124 subscribers.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    1h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                    <a class="list-group-item text-reset" href="#!">
                        <div class="row">
                            <div class="col-auto">

                                <!-- Avatar -->
                                <div class="avatar avatar-sm avatar-offline">
                                    <img class="avatar-img rounded-circle" src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." />
                                </div>

                            </div>
                            <div class="col ms-n2">

                                <!-- Heading -->
                                <h5 class="mb-1">
                                    Adolfo Hess
                                </h5>

                                <!-- Text -->
                                <p class="small text-gray-700 mb-0">
                                    Exported sales data from Launchday's subscriber data.
                                </p>

                                <!-- Time -->
                                <small class="text-muted">
                                    3h ago
                                </small>

                            </div>
                        </div> <!-- / .row -->
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- NAVIGATION -->
<nav class="navbar navbar-vertical fixed-start navbar-expand-md navbar-light" id="sidebar">
    <div class="container-fluid">

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse" aria-controls="sidebarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Brand -->
        <a class="navbar-brand" href="{{route('root')}}">
            <img src="{{asset('img/logo_light.svg')}}" class="navbar-brand-img mx-auto" alt="...">
        </a>

        <!-- User (xs) -->
        <div class="navbar-user d-md-none">

            <!-- Dropdown -->
            <div class="dropdown">

                <!-- Toggle -->
                <a href="#" id="sidebarIcon" class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="avatar avatar-sm avatar-online">
                        <span class="avatar-title rounded-circle">CC</span>
                    </div>
                </a>

                <!-- Menu -->
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="sidebarIcon">
                    <a href="./profile-posts.html" class="dropdown-item">Profile</a>
                    <a href="./account-general.html" class="dropdown-item">Settings</a>
                    <hr class="dropdown-divider">

                    <a href="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}"  class="dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Salir
                    </a>

                    <form id="logout-form" action="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>

            </div>

        </div>

        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidebarCollapse">

            <!-- Form -->
            <form class="mt-4 mb-3 d-md-none">
                <div class="input-group input-group-rounded input-group-merge input-group-reverse">
                    <input class="form-control" type="search" placeholder="Buscar" aria-label="Search">
                    <div class="input-group-text">
                        <span class="fe fe-search"></span>
                    </div>
                </div>
            </form>

            <!-- Navigation -->

            <x-menucommon/>
            <x-menustudent/>



            <div class="mt-auto"></div>


            <!-- User (md) -->
            <div class="navbar-user d-none d-md-flex" id="sidebarUser">

                <!-- Bottom Notification -->
                <a class="navbar-user-link" data-bs-toggle="offcanvas" href="#sidebarOffcanvasActivity" aria-controls="sidebarOffcanvasActivity">
                <span class="icon">
                  <i class="fe fe-bell"></i>
                </span>
                </a>

                <!-- Dropup -->
                <div class="dropup">

                    <!-- Toggle -->
                    <a href="#" id="sidebarIconCopy" class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-sm avatar-online">
                            <span class="avatar-title rounded-circle">AA</span>
                        </div>
                    </a>

                    <!-- Menu -->
                    <div class="dropdown-menu" aria-labelledby="sidebarIconCopy">
                        <a href="./profile-posts.html" class="dropdown-item">Profile</a>
                        <a href="./account-general.html" class="dropdown-item">Settings</a>

                        <hr class="dropdown-divider">

                        <a href="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}"  class="dropdown-item"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Salir
                        </a>

                        <form id="logout-form" action="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>

                </div>

                <!-- Icon -->
                <a class="navbar-user-link" data-bs-toggle="offcanvas" href="#sidebarOffcanvasSearch" aria-controls="sidebarOffcanvasSearch">
                <span class="icon">
                  <i class="fe fe-search"></i>
                </span>
                </a>

            </div>

        </div> <!-- / .navbar-collapse -->

    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="main-content">

    <nav class="navbar navbar-expand-md navbar-light d-none d-md-flex" id="topbar">
        <div class="container-fluid">

            <!-- Form -->
            <form class="form-inline me-4 d-none d-md-flex">
                <div class="input-group input-group-flush input-group-merge input-group-reverse" data-list="{&quot;valueNames&quot;: [&quot;name&quot;]}">

                    <!-- Input -->
                    <input type="search" class="form-control dropdown-toggle list-search" data-bs-toggle="dropdown" placeholder="Buscar" aria-label="Search">

                    <!-- Prepend -->
                    <div class="input-group-text">
                        <i class="fe fe-search"></i>
                    </div>

                    <!-- Menu -->
                    <div class="dropdown-menu dropdown-menu-card">
                        <div class="card-body">

                            <!-- List group -->
                            <div class="list-group list-group-flush list-group-focus list my-n3"><a class="list-group-item" href="./team-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/teams/team-logo-1.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Airbnbbb
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 2hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./team-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/teams/team-logo-2.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Medium Corporation
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 2hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./project-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-4by3">
                                                <img src="./assets/img/avatars/projects/project-1.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Homepage Redesign
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 4hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./project-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-4by3">
                                                <img src="./assets/img/avatars/projects/project-2.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Travels &amp; Time
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 4hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./project-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-4by3">
                                                <img src="./assets/img/avatars/projects/project-3.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Safari Exploration
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 4hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./profile-posts.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Dianna Smiley
                                            </h4>

                                            <!-- Status -->
                                            <p class="text-body small mb-0"><span class="text-success">●</span> Online</p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./profile-posts.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Ab Hadley
                                            </h4>

                                            <!-- Status -->
                                            <p class="text-body small mb-0"><span class="text-danger">●</span> Offline</p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a></div>
                        </div>
                    </div> <!-- / .dropdown-menu -->

                </div>
            </form>

            <!-- User -->
            <div class="navbar-user">

                <!-- Dropdown -->
                <div class="dropdown me-4 d-none d-md-flex">

                    <!-- Toggle -->
                    <a href="#" class="navbar-user-link" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <span class="icon">
                        <i class="fe fe-bell"></i>
                      </span>
                    </a>

                    <!-- Menu -->
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-card">
                        <div class="card-header">

                            <!-- Title -->
                            <h5 class="card-header-title">
                                Notifications
                            </h5>

                            <!-- Link -->
                            <a href="#!" class="small">
                                View all
                            </a>

                        </div> <!-- / .card-header -->
                        <div class="card-body">

                            <!-- List group -->
                            <div class="list-group list-group-flush list-group-activity my-n3">
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Dianna Smiley</strong> shared your post with Ab Hadley, Adolfo Hess,
                                                and 3 others.
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Ab Hadley</strong> reacted to your post with a 😍
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Adolfo Hess</strong> commented
                                                <blockquote class="mb-0">
                                                    “I don’t think this really makes sense to do without approval from Johnathan
                                                    since he’s the one...”
                                                </blockquote>
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-4.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small"><strong>Daniela Dewitt</strong> subscribed to you.</div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-5.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Miyah Myles</strong> shared your post with Ryu Duke, Glen Rouse, and 3 others.
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-6.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Ryu Duke</strong> reacted to your post with a 😍
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-7.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Glen Rouse</strong> commented
                                                <blockquote class="mb-0">
                                                    “I don’t think this really makes sense to do without approval from Johnathan
                                                    since he’s the one...”
                                                </blockquote>
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-8.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small"><strong>Grace Gross</strong> subscribed to you.</div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                            </div>
                        </div>
                    </div> <!-- / .dropdown-menu -->
                </div>

                <!-- Dropdown -->
                <div class="dropdown">

                    <!-- Toggle -->
                    <a href="#" class="avatar avatar-sm avatar-online dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar-title rounded-circle">BB</span>
                    </a>

                    <!-- Menu -->
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="./profile-posts.html" class="dropdown-item">Profile</a>
                        <a href="./account-general.html" class="dropdown-item">Settings</a>
                        <hr class="dropdown-divider">

                        <a href="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}"  class="dropdown-item"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Salir
                        </a>

                        <form id="logout-form" action="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}" method="POST" style="display: none;">
                            @csrf
                        </form>

                    </div>

                </div>

            </div>

        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <!-- Header -->
                <div class="header">
                    <div class="header-body">
                        <div class="row align-items-center">
                            <div class="col">

                                <!-- Pretitle -->
                                <h6 class="header-pretitle">
                                    @yield('subtitle')
                                </h6>

                                <!-- Title -->
                                <h1 class="header-title">
                                    @yield('title')
                                </h1>

                            </div>
                        </div>
                        @yield('submenu')
                    </div>
                </div>

                @yield('content')


            </div>
        </div> <!-- / .row -->
    </div>

</div>

{{--

<div class="main-content">

    <nav class="navbar navbar-expand-md navbar-light d-none d-md-flex" id="topbar">
        <div class="container-fluid">

            <!-- Form -->
            <form class="form-inline me-4 d-none d-md-flex">
                <div class="input-group input-group-flush input-group-merge input-group-reverse" data-list="{&quot;valueNames&quot;: [&quot;name&quot;]}">

                    <!-- Input -->
                    <input type="search" class="form-control dropdown-toggle list-search" data-bs-toggle="dropdown" placeholder="Buscar" aria-label="Search">

                    <!-- Prepend -->
                    <div class="input-group-text">
                        <i class="fe fe-search"></i>
                    </div>

                    <!-- Menu -->
                    <div class="dropdown-menu dropdown-menu-card">
                        <div class="card-body">

                            <!-- List group -->
                            <div class="list-group list-group-flush list-group-focus list my-n3"><a class="list-group-item" href="./team-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/teams/team-logo-1.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Airbnbbb
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 2hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./team-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/teams/team-logo-2.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Medium Corporation
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 2hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./project-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-4by3">
                                                <img src="./assets/img/avatars/projects/project-1.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Homepage Redesign
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 4hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./project-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-4by3">
                                                <img src="./assets/img/avatars/projects/project-2.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Travels &amp; Time
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 4hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./project-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-4by3">
                                                <img src="./assets/img/avatars/projects/project-3.jpg" alt="..." class="avatar-img rounded">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Safari Exploration
                                            </h4>

                                            <!-- Time -->
                                            <p class="small text-muted mb-0">
                                                <span class="fe fe-clock"></span>
                                                <time datetime="2018-05-24">Updated 4hr ago</time>
                                            </p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./profile-posts.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Dianna Smiley
                                            </h4>

                                            <!-- Status -->
                                            <p class="text-body small mb-0"><span class="text-success">●</span> Online</p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a><a class="list-group-item" href="./profile-posts.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">
                                                <img src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Title -->
                                            <h4 class="text-body text-focus mb-1 name">
                                                Ab Hadley
                                            </h4>

                                            <!-- Status -->
                                            <p class="text-body small mb-0"><span class="text-danger">●</span> Offline</p>

                                        </div>
                                    </div> <!-- / .row -->
                                </a></div>
                        </div>
                    </div> <!-- / .dropdown-menu -->

                </div>
            </form>

            <!-- User -->
            <div class="navbar-user">

                <!-- Dropdown -->
                <div class="dropdown me-4 d-none d-md-flex">

                    <!-- Toggle -->
                    <a href="#" class="navbar-user-link" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <span class="icon">
                        <i class="fe fe-bell"></i>
                      </span>
                    </a>

                    <!-- Menu -->
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-card">
                        <div class="card-header">

                            <!-- Title -->
                            <h5 class="card-header-title">
                                Notifications
                            </h5>

                            <!-- Link -->
                            <a href="#!" class="small">
                                View all
                            </a>

                        </div> <!-- / .card-header -->
                        <div class="card-body">

                            <!-- List group -->
                            <div class="list-group list-group-flush list-group-activity my-n3">
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-1.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Dianna Smiley</strong> shared your post with Ab Hadley, Adolfo Hess,
                                                and 3 others.
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-2.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Ab Hadley</strong> reacted to your post with a 😍
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-3.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Adolfo Hess</strong> commented
                                                <blockquote class="mb-0">
                                                    “I don’t think this really makes sense to do without approval from Johnathan
                                                    since he’s the one...”
                                                </blockquote>
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-4.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small"><strong>Daniela Dewitt</strong> subscribed to you.</div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-5.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Miyah Myles</strong> shared your post with Ryu Duke, Glen Rouse, and 3 others.
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-6.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Ryu Duke</strong> reacted to your post with a 😍
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-7.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small">
                                                <strong>Glen Rouse</strong> commented
                                                <blockquote class="mb-0">
                                                    “I don’t think this really makes sense to do without approval from Johnathan
                                                    since he’s the one...”
                                                </blockquote>
                                            </div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                                <a class="list-group-item text-reset" href="#!">
                                    <div class="row">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar avatar-sm">
                                                <img src="./assets/img/avatars/profiles/avatar-8.jpg" alt="..." class="avatar-img rounded-circle">
                                            </div>

                                        </div>
                                        <div class="col ms-n2">

                                            <!-- Content -->
                                            <div class="small"><strong>Grace Gross</strong> subscribed to you.</div>

                                            <!-- Time -->
                                            <small class="text-muted">
                                                2m ago
                                            </small>

                                        </div>
                                    </div> <!-- / .row -->
                                </a>
                            </div>
                        </div>
                    </div> <!-- / .dropdown-menu -->
                </div>

                <!-- Dropdown -->
                <div class="dropdown">

                    <!-- Toggle -->
                    <a href="#" class="avatar avatar-sm avatar-online dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar-title rounded-circle">BB</span>
                    </a>

                    <!-- Menu -->
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="./profile-posts.html" class="dropdown-item">Profile</a>
                        <a href="./account-general.html" class="dropdown-item">Settings</a>
                        <hr class="dropdown-divider">

                        <a href="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}"  class="dropdown-item"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Salir
                        </a>

                        <form id="logout-form" action="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}" method="POST" style="display: none;">
                            @csrf
                        </form>

                    </div>

                </div>

            </div>

        </div>
    </nav>

    <!-- HEADER -->
    <div class="header">
        <div class="container-fluid">

            <!-- Body -->
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">

                        <!-- Pretitle -->
                        <h6 class="header-pretitle">
                           @yield('subtitle')
                        </h6>

                        <!-- Title -->
                        <h1 class="header-title">
                            @yield('title')
                        </h1>

                    </div>
                    <div class="col-auto">

                    </div>
                </div> <!-- / .row -->
            </div> <!-- / .header-body -->

        </div>
    </div> <!-- / .header -->

    <!-- CARDS -->
    <div class="container-fluid">
        @yield('content')
    </div>

</div><!-- / .main-content -->

--}}

<!-- JAVASCRIPT -->
<script src="{{asset('js/manifest.js')}}"></script>
<script src="{{asset('js/vendor.js')}}"></script>
<script src="{{asset('js/app.js')}}"></script>

<!-- Map JS -->
<script src='https://api.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.js'></script>

<!-- Vendor JS -->
<script src="{{ asset('js/vendor.bundle.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('js/theme.bundle.js') }}"></script>

<!-- Show password -->
<script src="{{asset('js/show-password.js')}}"></script>

<!-- Alerts -->
<script src="{{asset('js/alerts.js')}}"></script>

</body>
</html>
