<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <ul class="navbar-nav">

        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>

        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{route('updates.list',\Instantiation::instance())}}" class="nav-link">Actualizaciones</a>
        </li>

        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{route('suggestionsmailbox',\Instantiation::instance())}}" class="nav-link">Buzón</a>
        </li>

    </ul>


    <ul class="navbar-nav ml-auto">


        <img width="23" height="23" src="{{Auth::user()->avatar_route()}}" class="img-circle elevation-2"
             style="margin: 7px 0px 0px 0px"
             alt="User Image">


        <!-- Logout -->
        <li class="nav-item">
            <a href="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}"  class="nav-link"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="nav-icon fas fa-sign-out-alt"></i>
            </a>
        </li>
        <form id="logout-form" action="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}" method="POST" style="display: none;">
            @csrf
        </form>

    </ul>

</nav>
