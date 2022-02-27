@props([
'icon' => 'grid',
'name' => 'Undefined',
'badge' => null
])

@php

$active = "";
if(Route::currentRouteName() == $route){
    $active = "active";
}

$isThereBadge = false;

if($badge){
    $isThereBadge = true;
}

@endphp

<li class="nav-item">
    <a class="nav-link {{$active}}" href="{{route("$route")}}">
        <i class="fe fe-{{$icon}}"></i> {{$name}}

        @if($isThereBadge)
            <span class="badge bg-primary ms-auto">{{$badge}}</span>
        @endif


    </a>
</li>