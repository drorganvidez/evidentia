<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Software libre para la gestión de evidencias de trabajo en jornadas docentes" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>

    <!-- Base CSS -->
    <link rel="stylesheet" href="{{asset('css/app.css')}}" />

    <!-- Libs CSS -->
    <link rel="stylesheet" href="{{ asset('css/libs.bundle.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/theme.bundle.css') }}" />

    <!-- Title -->
    <title>@yield('title') | Evidentia Cloud</title>
</head>
<body class="d-flex align-items-center bg-auth border-top border-top-2 border-primary">

<x-alert></x-alert>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5 col-xl-4 my-5">

            <!-- Logo -->
            <div class="display-4 text-center mb-3">
                <a href="{{route('root')}}">
                    <img width="100px" src="{{asset('img/logo_light.svg')}}">
                </a>
            </div>

            <!-- Heading -->
            <h1 class="display-4 text-center mb-3">
                @yield('title')
            </h1>

            <!-- Subheading -->
            <p class="text-muted text-center mb-5">
                {{\Instantiation::instance_entity()?->name}}
            </p>

        @yield('content')

        <!-- Link -->
            <div class="text-center">

                <small class="text-muted text-center">
                    ¿Nuev@ en Evidentia? <a href="sign-up.html">Date de alta</a>.
                    <br>
                    <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU/GPL 3.0</a> •
                    <a href="https://github.com/drorganvidez/evidentia">Repositorio en GitHub</a> •
                    <a data-bs-toggle="modal" data-bs-target="#modalMembers" href="#">Acerca de</a>
                    <br>
                    <a href="https://github.com/drorganvidez/evidentia/releases">Versión {{env('EVIDENTIA_VERSION')}}</a>
                    •
                    <a href="{{route('admin.login')}}">Administración</a>

                </small>
            </div>

            <div class="modal fade" id="modalMembers" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-card card">
                            <div class="card-header">

                                <!-- Title -->
                                <h4 class="card-header-title" id="exampleModalCenterTitle">
                                    Acerca de
                                </h4>

                                <!-- Close -->
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                            </div>
                            <div class="card-body">

                                <h3>¿Qué es?</h3>

                                <p>
                                    <b>evidentia.cloud</b> es un sistema de gestión automática de evidencias de trabajos de alumnos
                                    y alumnas. Fue originalmente pensado para las Jornadas InnoSoft Days organizadas en el ámbito de la asignatura
                                    Evolución y Gestión de la Configuración.
                                </p>

                                <hr>

                                <h3>Software</h3>

                                <p>
                                    Licencia GNU General Public License v3
                                    (<a href="https://www.gnu.org/licenses/gpl-3.0.html">más información</a>)
                                    <br>
                                    <a href="https://github.com/drorganvidez/evidentia">Repositorio de GitHub</a>
                                </p>

                                <hr>

                                <h3>Responsable principal</h3>

                                <p>
                                    David Romero Organvídez
                                    <br>
                                    drorganvidez(arroba)us.es
                                    <br>
                                    Departamento de Lenguajes y Sistemas Informáticos
                                    <br>
                                    Universidad de Sevilla
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-default">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Acerca de</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img width="100px" src="{{asset('dist/img/logo_light.svg')}}">
                            <p class="text-muted">
                                <b>evidentia.cloud</b> es un sistema de gestión automática de evidencias de trabajos de alumnos
                                y alumnas. Fue originalmente pensado para las Jornadas InnoSoft Days organizadas en el ámbito de la asignatura
                                Evolución y Gestión de la Configuración.
                            </p>

                            <div class="text-muted">

                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody><tr>
                                            <th>Licencia software</th>
                                            <td>GNU General Public License v3
                                                (<a href="https://www.gnu.org/licenses/gpl-3.0.html">más información</a>)</td>
                                        </tr>
                                        <tr>
                                            <th>Idea original</th>
                                            <td>Romero Organvídez, David</td>
                                        </tr>
                                        <tr>
                                            <th>Código fuente</th>
                                            <td>
                                                <a class="btn btn-dark btn-sm" style="color: white; text-decoration: none" href="https://github.com/drorganvidez/evidentia">
                                                    <i class="fab fa-github"></i>
                                                    Repositorio de GitHub
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer justify-content-right">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->




        </div>
    </div> <!-- / .row -->
</div> <!-- / .container -->

<!-- JAVASCRIPT -->
<script src="{{asset('js/manifest.js')}}"></script>
<script src="{{asset('js/vendor.js')}}"></script>
<script src="{{asset('js/app.js')}}"></script>

<!-- Vendor JS -->
<script src="{{ asset('js/vendor.bundle.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('js/theme.bundle.js') }}"></script>

<!-- Show password -->
<script src="{{asset('js/show-password.js')}}"></script>

<!-- Alerts -->
<script src="{{asset('js/alerts.js')}}"></script>

@yield('scripts')

<script>

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

</body>
</html>
