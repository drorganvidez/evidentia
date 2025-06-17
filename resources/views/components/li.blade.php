<li class="nav-item">
    @if(Request::is('admin') || Request::is('admin/*'))
        <a href="{{ route($route) }}"
    @else
        <a href="{{ route($route) }}"
    @endif


       class="nav-link

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
