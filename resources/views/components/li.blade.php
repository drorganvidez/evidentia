<li class="nav-item">
    <a href="{{ route($route,$instance) }}" class="nav-link

    @foreach($secondaries as $secondary)
        @if(Route::currentRouteName() == $secondary)
            active
        @endif
    @endforeach

    {{ (Route::currentRouteName() == $route) ? 'active' : '' }}">
        <i class="nav-icon {{$icon}}"></i>
        <p>
            {{$name}}
        </p>
    </a>
</li>
