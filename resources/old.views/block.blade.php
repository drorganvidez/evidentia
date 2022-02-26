<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Ups | Evidentia</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition lockscreen">
<!-- Automatic element centering -->
<div class="lockscreen-wrapper">
    <div class="lockscreen-logo">
        <x-lilogo/>
    </div>
    <!-- User name -->
    <div class="lockscreen-name">{{Auth::user()->name}}, lo sentimos :(</div>

    <div class="help-block text-center">
        Tu cuenta ha sido bloqueada por alguna razón. Contacta con tu profesor o con el presidente
        de las jornadas de este año.
    </div>

    <div class="text-center">
        <br>
        <a href="{{ route('logout') }}"  class="nav-link"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="nav-icon fas fa-sign-out-alt"></i> Cerrar sesión
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

    </div>
    <div class="lockscreen-footer text-center">
        GNU/GPL 3.0 · <a href="https://github.com/drorganvidez/evidentia"><i class="fab fa-github"></i> Repositorio en GitHub</a>
        <br>
        Hecho con <i class="fas fa-heart"></i>
    </div>
</div>
<!-- /.center -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
