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

<div class="row align-items-center">
    <div class="col">

        <!-- Nav -->
        <ul class="nav nav-tabs nav-overflow header-tabs">

            @php

                for($i = 0; $i < count($subitems_names); $i++){

                    // activar la ruta actual
                    $active = "";

                    if(Route::currentRouteName() == $subitems_routes[$i]){
                        $active = "active";
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
</div>