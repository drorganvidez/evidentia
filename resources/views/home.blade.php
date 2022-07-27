@extends('layouts.app')

@section('title', 'Dashboard')

@section('subtitle', 'Bienvenid@')

@section('content')

    <div class="row">
        <div class="col-lg-6">

            <div class="card">

                <!-- Dropdown -->
                <div class="dropdown card-dropdown">
                    <a href="#" class="dropdown-ellipses dropdown-toggle text-white" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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

                <!-- Image -->
                <img src="assets/img/covers/profile-cover-2.jpg" alt="..." class="card-img-top">

                <!-- Body -->
                <div class="card-body">

                    <!-- Image -->
                    <a href="profile-posts.html" class="avatar avatar-xl card-avatar card-avatar-top">
                        <img src="assets/img/avatars/profiles/avatar-2.jpg" class="avatar-img rounded-circle border border-4 border-card" alt="...">
                    </a>

                    <!-- Heading -->
                    <h2 class="card-title text-center">
                        <a href="profile-posts.html">Ab Hadley</a>
                    </h2>

                    <!-- Text -->
                    <p class="small text-center text-muted mb-3">
                        Woprking on the latest API integration.
                    </p>

                    <!-- Badges -->
                    <p class="text-center mb-4">
                      <span class="badge bg-secondary-soft">
                        UX Team
                      </span>
                        <span class="badge bg-secondary-soft">
                        Research Team
                      </span>
                    </p>

                    <!-- Stats -->
                    <div class="row g-0 border-top border-bottom">
                        <div class="col-lg-3 col-sm-6 col-md-6 py-4 text-center">

                            <!-- Heading -->
                            <h6 class="text-uppercase text-muted">
                                Evidencias
                            </h6>

                            <!-- Value -->
                            <h2 class="mb-0">10.2k</h2>

                        </div>
                        <div class="col-lg-3 col-sm-6 col-md-6 py-4 text-center border-start">

                            <!-- Heading -->
                            <h6 class="text-uppercase text-muted">
                                Reuniones
                            </h6>

                            <!-- Value -->
                            <h2 class="mb-0">2.7k</h2>

                        </div>
                        <div class="col-lg-3 col-sm-6 col-md-6 py-4 text-center border-start">

                            <!-- Heading -->
                            <h6 class="text-uppercase text-muted">
                                Asistencias
                            </h6>

                            <!-- Value -->
                            <h2 class="mb-0">2.7k</h2>

                        </div>
                        <div class="col-lg-3 col-sm-6 col-md-6 py-4 text-center border-start">

                            <!-- Heading -->
                            <h6 class="text-uppercase text-muted">
                                Bonificadas
                            </h6>

                            <!-- Value -->
                            <h2 class="mb-0">2.7k</h2>

                        </div>
                    </div>

                    <!-- List group -->
                    <div class="list-group list-group-flush mb-4">
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">

                                    <!-- Title -->
                                    <small>Joined</small>

                                </div>
                                <div class="col-auto">

                                    <!-- Time -->
                                    <time class="small text-muted" datetime="1988-10-24">
                                        10/24/88
                                    </time>

                                </div>
                            </div> <!-- / .row -->
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">

                                    <!-- Title -->
                                    <small>Location</small>

                                </div>
                                <div class="col-auto">

                                    <!-- Text -->
                                    <small class="text-muted">
                                        Los Angeles, CA
                                    </small>

                                </div>
                            </div> <!-- / .row -->
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col">

                                    <!-- Title -->
                                    <small>Product</small>

                                </div>
                                <div class="col-auto">

                                    <!-- Link -->
                                    <a class="small text-muted">
                                        Landkit
                                    </a>

                                </div>
                            </div> <!-- / .row -->
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto">

                            <!-- Status -->
                            <small>
                                <span class="text-success">●</span> Online
                            </small>

                        </div>
                        <div class="col-auto">

                            <!-- Link -->
                            <a href="#!" class="btn btn-sm btn-primary">
                                Subscribe
                            </a>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="row">

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">

                                    <!-- Avatar -->
                                    <a href="project-overview.html" class="avatar avatar-lg avatar-4by3">
                                        <img src="assets/img/avatars/projects/project-1.jpg" alt="..." class="avatar-img rounded">
                                    </a>

                                </div>
                                <div class="col ms-n2">

                                    <!-- Title -->
                                    <h4 class="mb-1">
                                        <a href="project-overview.html">Homepage Redesign</a>
                                    </h4>

                                    <!-- Text -->
                                    <p class="small text-muted mb-1">
                                        <time datetime="2018-06-21">Updated 2hr ago</time>
                                    </p>

                                    <!-- Progress -->
                                    <div class="row align-items-center g-0">
                                        <div class="col-auto">

                                            <!-- Value -->
                                            <div class="small me-2">29%</div>

                                        </div>
                                        <div class="col">

                                            <!-- Progress -->
                                            <div class="progress progress-sm">
                                                <div class="progress-bar" role="progressbar" style="width: 29%" aria-valuenow="29" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>

                                        </div>
                                    </div> <!-- / .row -->

                                </div>
                                <div class="col-auto">

                                    <!-- Avatar group -->
                                    <div class="avatar-group d-none d-md-inline-flex">
                                        <a href="profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="" data-bs-original-title="Ab Hadley">
                                            <img src="assets/img/avatars/profiles/avatar-2.jpg" class="avatar-img rounded-circle" alt="...">
                                        </a>
                                        <a href="profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="" data-bs-original-title="Adolfo Hess">
                                            <img src="assets/img/avatars/profiles/avatar-3.jpg" class="avatar-img rounded-circle" alt="...">
                                        </a>
                                        <a href="profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="" data-bs-original-title="Daniela Dewitt">
                                            <img src="assets/img/avatars/profiles/avatar-4.jpg" class="avatar-img rounded-circle" alt="...">
                                        </a>
                                        <a href="profile-posts.html" class="avatar avatar-xs" data-bs-toggle="tooltip" title="" data-bs-original-title="Miyah Myles">
                                            <img src="assets/img/avatars/profiles/avatar-5.jpg" class="avatar-img rounded-circle" alt="...">
                                        </a>
                                    </div>

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
                        </div> <!-- / .card-body -->
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">

                                    <!-- Title -->
                                    <h6 class="text-uppercase text-muted mb-2">
                                        Weekly Sales
                                    </h6>

                                    <!-- Heading -->
                                    <span class="h2 mb-0">
                          $24,500
                        </span>

                                    <!-- Badge -->
                                    <span class="badge bg-success-soft mt-n1">
                          +3.5%
                        </span>

                                </div>
                                <div class="col-auto">

                                    <!-- Icon -->
                                    <span class="h2 fe fe-dollar-sign text-muted mb-0"></span>

                                </div>
                            </div> <!-- / .row -->

                        </div>
                    </div>

                    <!-- Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">

                                    <!-- Title -->
                                    <h6 class="text-uppercase text-muted mb-2">
                                        Orders Placed
                                    </h6>

                                    <!-- Heading -->
                                    <span class="h2 mb-0">
                          763.5
                        </span>

                                </div>
                                <div class="col-auto">

                                    <!-- Icon -->
                                    <span class="h2 fe fe-briefcase text-muted mb-0"></span>

                                </div>
                            </div> <!-- / .row -->

                        </div>
                    </div>

                    <!-- Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">

                                    <!-- Title -->
                                    <h6 class="text-uppercase text-muted mb-2">
                                        Conversion Rate
                                    </h6>

                                    <div class="row align-items-center g-0">
                                        <div class="col-auto">

                                            <!-- Heading -->
                                            <span class="h2 me-2 mb-0">
                              84.5%
                            </span>

                                        </div>
                                        <div class="col">

                                            <!-- Progress -->
                                            <div class="progress progress-sm">
                                                <div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>

                                        </div>
                                    </div> <!-- / .row -->

                                </div>
                                <div class="col-auto">

                                    <!-- Icon -->
                                    <span class="h2 fe fe-clipboard text-muted mb-0"></span>

                                </div>
                            </div> <!-- / .row -->

                        </div>
                    </div>

                    <!-- Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">

                                    <!-- Title -->
                                    <h6 class="text-uppercase text-muted mb-2">
                                        Avg. Order VaLue
                                    </h6>

                                    <!-- Heading -->
                                    <span class="h2 mb-0">
                          $85.50
                        </span>

                                </div>
                                <div class="col-auto">

                                    <!-- Chart -->
                                    <div class="chart chart-sparkline">
                                        <canvas class="chart-canvas" id="sparklineChart" width="187" height="87" style="display: block; box-sizing: border-box; height: 34.8px; width: 74.8px;"></canvas>
                                    </div>

                                </div>
                            </div> <!-- / .row -->

                        </div>
                    </div>
                </div>

            </div>


        </div>

    </div>


@endsection
