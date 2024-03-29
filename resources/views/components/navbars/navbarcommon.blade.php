<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <ul class="navbar-nav">

        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>

        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('updates.list',['instance' => \Instantiation::instance()]) }}" class="nav-link">
                <i class="fab fa-github"></i> Versión del software: {{env('EVIDENTIA_VERSION','')}}</a>
        </li>

        <!--
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{route('updates.list',\Instantiation::instance())}}" class="nav-link">Actualizaciones</a>
        </li>

        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{route('suggestionsmailbox',\Instantiation::instance())}}" class="nav-link">Buzón</a>
        </li>
        -->

    </ul>


    <ul class="navbar-nav ml-auto">

        <!-- Logout -->
        <li class="nav-item">
            <a href="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}"  class="nav-link"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="nav-icon fas fa-sign-out-alt"></i> Salir
            </a>
        </li>
        <form id="logout-form" action="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}" method="POST" style="display: none;">
            @csrf
        </form>

    </ul>

</nav>
