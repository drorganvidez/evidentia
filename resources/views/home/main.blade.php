<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Software libre para la gestión de evidencias de trabajo en jornadas docentes" />

    <title>@yield('title') | Evidentia</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">

    <!-- Theme style -->
    <link href="{{ asset('dist/css/adminlte.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dist/css/style.css') }}" rel="stylesheet">

    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.css') }}">

    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <!-- Select 2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- Bootstrap4 Duallistbox -->
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap4-duallistbox/bootstrap-duallistbox.css') }}">

    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">

</head>

<body class="hold-transition login-page">
    <div class="login-box">

        <div class="login-logo">
            <a href="/"><img width="200px" src="{{ asset('dist/img/logo_light.svg') }}"></a>
        </div>

        @yield('content')

        <div class="lockscreen-footer text-center" style="font-size: 10px">
            <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU/GPL 3.0</a> ·
            <a href="https://github.com/drorganvidez/evidentia">Repositorio en GitHub</a> ·
            <a data-toggle="modal" data-target="#modal-default" href="#">Acerca de</a>
            <br>
            Hecho con <i class="fas fa-heart"></i>
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
                        <img height="100px" class="img" src="{{ asset('dist/img/logo_light.svg') }}">
                        <p class="text-muted">
                            <b>evidentia.us.es</b> es un sistema de gestión automática de evidencias de trabajos de
                            alumnos
                            y alumnas. Fue originalmente pensado para las Jornadas InnoSoft Days organizadas en el
                            ámbito de la asignatura
                            Evolución y Gestión de la Configuración.
                        </p>

                        <div class="text-muted">

                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th>Licencia software</th>
                                            <td>GNU General Public License v3
                                                (<a href="https://www.gnu.org/licenses/gpl-3.0.html">más
                                                    información</a>)</td>
                                        </tr>
                                        <tr>
                                            <th>Idea original</th>
                                            <td>Romero Organvídez, David</td>
                                        </tr>
                                        <tr>
                                            <th>Código fuente</th>
                                            <td>
                                                <a class="btn btn-dark btn-sm"
                                                    style="color: white; text-decoration: none"
                                                    href="https://github.com/drorganvidez/evidentia">
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
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>

    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Bootstrap4 Duallistbox -->
    <script src="{{ asset('plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>

    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

    <!-- Selectors -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    <!-- File Input -->
    <link href="{{ asset('dist/css/fileinput.css') }}" media="all" rel="stylesheet" type="text/css" />
    <link href="{{ asset('dist/themes/explorer/theme.css') }}" media="all" rel="stylesheet" type="text/css" />
    <script src="{{ asset('dist/js/plugins/piexif.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('dist/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('dist/js/plugins/purify.min.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="{{ asset('dist/js/fileinput.js') }}"></script>
    <script src="{{ asset('dist/themes/fas/theme.js') }}"></script>
    <script src="{{ asset('dist/themes/explorer/theme.js') }}"></script>
    <script src="{{ asset('dist/js/fileinput_locales/es.js') }}"></script>

    <script>
        // estados
        @if (session('success'))

            $(document).Toasts('create', {
                title: '¡Felicidades!',
                icon: 'fas fa-check-circle',
                class: 'bg-success',
                autohide: true,
                delay: 7000,
                body: '{!! session('success') !!}'
            });
        @endif

        @if (session('error'))

            $(document).Toasts('create', {
                title: '¡Error!',
                icon: 'icon fas fa-ban',
                class: 'bg-danger',
                autohide: true,
                delay: 7000,
                body: '{!! session('error') !!}'
            });
        @endif

        @if (session('warning'))

            $(document).Toasts('create', {
                title: '¡Aviso!',
                icon: 'icon fas fa-ban',
                class: 'bg-danger',
                autohide: true,
                delay: 7000,
                body: '{!! session('warning') !!}'
            });
        @endif

        @if (session('light'))

            $(document).Toasts('create', {
                title: 'Restablecer contraseña',
                icon: 'fas fa-info',
                class: 'bg-light',
                autohide: true,
                delay: 7000,
                body: '{!! session('light') !!}'
            });
        @endif
    </script>

</body>

</html>
