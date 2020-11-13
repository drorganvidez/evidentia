<li class="nav-item">
    <a href="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}"  class="nav-link"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="nav-icon fas fa-sign-out-alt"></i>
        <p>
            Cerrar sesión
        </p>
    </a>
</li>

<form id="logout-form" action="{{ route('instance.logout',['instance' => \Instantiation::instance()]) }}" method="POST" style="display: none;">
    @csrf
</form>
