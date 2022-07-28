@php

    // obtenemos rutas, subrutas y badges
    $subitems_names = [];
    $subitems_routes = [];
    $badges = [];
    $badges_colors = [];
    $i = 0;
    foreach (explode(';', $subitems) as $item){
        $parts = explode(',', $item);
        $subitems_names[$i] = trim($parts[0]);
        $subitems_routes[$i] = trim($parts[1]);

        if(count($parts) == 3){
            $badges[$i] = trim($parts[2]);
            $badges_colors[$i] = "";
        }else if(count($parts) == 4){
            $badges[$i] = trim($parts[2]);
            $badges_colors[$i] = trim($parts[3]);
        }else {
            $badges[$i] = "";
            $badges_colors[$i] = "";
        }

        $i = $i + 1;
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
                            echo " $subitems_names[$i] ";

                            if($badges[$i] != "" && $badges[$i] != 0){

                                $badge_color = "secondary";

                                if(strcmp($badges_colors[$i], "danger") === 0 ){
                                    $badge_color = 'danger';
                                }

                                if(strcmp($badges_colors[$i], "success") === 0){
                                    $badge_color = 'success';
                                }

                                if(strcmp($badges_colors[$i], "warning") === 0){
                                    $badge_color = 'warning';
                                }

                                echo '<span class="badge rounded-pill bg-'.$badge_color.'-soft">'.$badges[$i].'</span>';
                            }

                        echo '</a>';
                    echo '</li>';
                }

            @endphp

        </ul>

    </div>
</div>