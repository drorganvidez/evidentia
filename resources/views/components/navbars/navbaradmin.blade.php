<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">

        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>

        <!-- Logout -->

    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a href="{{ route('instance.logout') }}" class="nav-link"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="nav-icon fas fa-sign-out-alt"></i> Salir
            </a>
        </li>
        <form id="logout-form" action="{{ route('instance.logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </ul>

</nav>
