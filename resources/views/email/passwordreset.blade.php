<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Software libre para la gestión de evidencias de trabajo en jornadas docentes" />

    <title>@yield('title') | Evidentia Cloud</title>

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

</head>

<body class="hold-transition sidebar-mini">

    <div class="wrapper">

        <!-- Content Wrapper. Contains page content -->
        <!-- Main content -->
        <section class="content">
            <div class="container">
                <div class="row">

                    <div
                        style="max-width: 600px; margin: 0 auto; padding: 2rem; font-family: Arial, sans-serif; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">

                        <div style="text-align: center; margin-bottom: 1.5rem;">
                            <img src="https://evidentia.us.es/dist/img/logo_light.svg" alt="Evidentia Logo"
                                style="max-width: 180px;">
                        </div>

                        <p style="font-size: 16px; color: #212529;">Hola, <strong>{{ $user->name }}</strong>:</p>

                        <p style="font-size: 15px; color: #212529;">
                            Has solicitado restablecer la contraseña de tu cuenta de <strong>Evidentia</strong>.
                            Si no has sido tú, puedes ignorar este mensaje sin hacer nada.
                        </p>

                        <div style="text-align: center; margin: 2rem 0;">
                            <a href="https://evidentia.us.es/password/update/{{ $token->token }}"
                                style="display: inline-block; padding: 12px 24px; background-color: #2e6da4; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                                Restablecer contraseña
                            </a>
                        </div>

                        <p style="font-size: 14px; color: #6c757d;">
                            Nota: el enlace de restablecimiento caduca en 24 horas.
                        </p>

                        <p style="font-size: 13px; font-style: italic; color: #6c757d;">
                            Por favor, no respondas a este correo. Esta dirección no está supervisada.
                        </p>
                    </div>

                </div>
            </div>
        </section>
        <!-- /.content -->

        <!-- /.content-wrapper -->

    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

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


    <!-- Summernote -->
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>


</body>

</html>
