<li class="nav-item">
    <a href="{{ route($route,$instance) }}" class="nav-link  {{ (Route::currentRouteName() == $route) ? 'active' : '' }}">
        <i class="nav-icon {{$icon}}"></i>
        <p>
            {{$name}}
        </p>
    </a>
</li>
