<li class="nav-item">
    <a href="{{ route($route,\Instantiation::instance()) }}" class="nav-link

    @isset($secondaries)
        @foreach(explode(',', $secondaries) as $secondary) 
            @if(Route::currentRouteName() == $secondary)
                active
            @endif
        @endforeach
    @endisset

    {{ (Route::currentRouteName() == $route) ? 'active' : '' }}">
        <i class="nav-icon {{$icon}}"></i>
        <p>
            {{$name}}
        </p>
    </a>
</li>
