<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Software libre para la gestión de evidencias de trabajo en jornadas docentes" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>

    <!-- Theme CSS -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Title -->
    <title>@yield('title') @yield('parent') | Evidentia Cloud</title>

    @livewireStyles
</head>
<body>


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
        <a class="text-center" href="{{route('root')}}">
            <x-logo-css/>
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
                        <a href="{{route('profile.data',\Instantiation::instance())}}" class="dropdown-item">Perfil</a>
                        <a href="{{route('settings.notifications',\Instantiation::instance())}}" class="dropdown-item">Ajustes</a>

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
            <x-menucoordinator/>



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

                        <a href="{{route('profile.data',\Instantiation::instance())}}" class="dropdown-item">Perfil</a>
                        <a href="{{route('settings.notifications',\Instantiation::instance())}}" class="dropdown-item">Ajustes</a>

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

    <!-- TOP BAR -->
    <nav class="navbar navbar-expand-md navbar-light d-none d-md-flex" id="topbar">
        <div class="container-fluid">

            <!-- TOP BAR SEARCH FORM -->
            <form class="form-inline me-4 d-none d-md-flex">
                <div class="input-group input-group-flush input-group-merge input-group-reverse" data-list="{&quot;valueNames&quot;: [&quot;name&quot;]}">

                    <!-- Input -->
                    <input type="search" class="form-control dropdown-toggle list-search" data-bs-toggle="dropdown" placeholder="Buscar" aria-label="Search">

                    <!-- Prepend -->
                    <div class="input-group-text">
                        <i class="fe fe-search"></i>
                    </div>

                    <!-- TOP BAR SEARCH RESULTS -->
                    <div class="dropdown-menu dropdown-menu-card">
                        <div class="card-body">

                            <!-- List group -->
                            <div class="list-group list-group-flush list-group-focus list my-n3">
                                <a class="list-group-item" href="./team-overview.html">
                                    <div class="row align-items-center">
                                        <div class="col-auto">

                                            <!-- Avatar -->
                                            <div class="avatar">

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
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div> <!-- / .dropdown-menu -->

                </div>
            </form>

            <!-- User -->
            <div class="navbar-user">

                <!-- Toggle -->
                @livewire('dark-mode')

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
                        <a href="{{route('profile.data',\Instantiation::instance())}}" class="dropdown-item">Perfil</a>
                        <a href="{{route('settings.notifications',\Instantiation::instance())}}" class="dropdown-item">Ajustes</a>
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

        <x-alert></x-alert>

        <div class="row justify-content-center">
            <div class="col-12 mb-3">

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
                            <div class="col-auto">

                                @yield('options')

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

<!-- JAVASCRIPT -->

<script src="{{asset('build/js/custom/custom.js')}}" ></script>

@stack('scripts')

@yield('scripts')

<script type="module">

    @if (session('error'))

    throw_alert('error');

    @endif

    @if (session('success'))

    throw_alert('success');

    @endif

    @if (session('light'))

    throw_alert('light', delay = 7000);

    @endif
</script>

@livewireScripts
<script> window.livewire_app_url = '{{route('home', \Instantiation::instance())}}'; </script>

</body>
</html>
