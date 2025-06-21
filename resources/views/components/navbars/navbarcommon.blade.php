<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <ul class="navbar-nav">

        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>

        @if ($version = evidentia_version())
            <li class="nav-item d-none d-sm-inline-block">
                <a href="https://github.com/drorganvidez/evidentia/releases/tag/{{ $version }}" class="nav-link" target="_blank">
                    <i class="fab fa-github"></i> Versión del software: {{ $version }}
                </a>
            </li>      
        @endif

    </ul>


    <ul class="navbar-nav ml-auto">

        <!-- Logout -->
        <li class="nav-item">
            <a href="{{ route('logout') }}" class="nav-link"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="nav-icon fas fa-sign-out-alt"></i> Salir
            </a>
        </li>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

    </ul>

</nav>
