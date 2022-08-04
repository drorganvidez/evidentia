@props([
'icon' => 'grid',
'name' => 'Undefined',
'badge' => null
])

@php



$isThereBadge = false;

if($badge){
    $isThereBadge = true;
}

@endphp

@isset($subitems)

    @php

        // obtenemos rutas y subrutas
        $subitems_names = [];
        $subitems_routes = [];
        $i = 0;
        foreach (explode(';', $subitems) as $item){
            $parts = explode(',', $item);
            $subitems_names[$i] = trim($parts[0]);
            $subitems_routes[$i] = trim($parts[1]);
            $i = $i + 1;
        }

        // comprobamos si alguna ruta actual es un subitem de este menú
        $parent_collapsed = "collapsed";
        $aria_expanded = "false";
        $show = "";
        for($i = 0; $i < count($subitems_routes); $i++){
            if(Route::currentRouteName() == $subitems_routes[$i]){
                $parent_collapsed = "";
                $aria_expanded = "true";
                $show = "show";
                break;
            }
        }

    @endphp

    <li class="nav-item">
        <a class="nav-link {{$parent_collapsed}}" href="#{{$route}}" data-bs-toggle="collapse" role="button" aria-expanded="{{$aria_expanded}}" aria-controls="{{$route}}">
            <i style="width: 18px; margin-right: 11px" data-feather="{{$icon}}"></i> {{$name}}
        </a>
        <div class="collapse {{$show}}" id="{{$route}}" style="">
            <ul class="nav nav-sm flex-column">

                @php

                    for($i = 0; $i < count($subitems_names); $i++){

                        // activar la ruta actual
                        $active = "";

                        if(Route::currentRouteName() == $subitems_routes[$i]){
                            $active = " active";
                        }

                        echo '<li class="nav-item">';
                            echo '<a href="'.route($subitems_routes[$i], \Instantiation::instance()).'" class="nav-link '.$active.'">';
                                echo $subitems_names[$i];
                            echo '</a>';
                        echo '</li>';
                    }

                @endphp
            </ul>
        </div>
    </li>

@else

    @php

    $active = "";

    if(Route::currentRouteName() == $route){
        $active = " active";
    }

    @endphp

    <li class="nav-item">
        <a class="nav-link{{$active}}" href="{{route("$route",\Instantiation::instance())}}">
            <i style="width: 18px; margin-right: 11px" data-feather="{{$icon}}"></i> {{$name}}

            @if($isThereBadge)
                <span class="badge bg-primary ms-auto">{{$badge}}</span>
            @endif

        </a>
    </li>

@endisset