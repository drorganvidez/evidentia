@php

    $data_string = (string) $data;
    $data_string = str_replace("&quot;",'"',$data_string);
    $data_array = json_decode($data_string, true);

@endphp

@if(count($data_array) > 0 or (count($data_array) === 0 and count(request()->query()) !== 0 ))
    @isset($columns)

        @php

            $column_fields = [];
            $column_values = [];
            $customizations = [];

            $i = 0;
            foreach (explode(';', $columns) as $item){
                $parts = explode('|', $item);
                $column_fields[$i] = trim($parts[0]);
                $column_values[$i] = trim($parts[1]);

                if(count($parts) == 3) {
                    $customizations[$i] = json_decode(trim($parts[2]), true);
                } else {
                    $customizations[$i] = json_decode("{}", true);
                }

                $i = $i + 1;
            }

        @endphp
    @endisset

    @isset($filters)
        @php

            $filter_fields = [];
            $filter_names = [];
            $filter_values = [];
            $i = 0;
            foreach (explode(';', $filters) as $item){
                $parts = explode(',', $item);
                $filter_fields[$i] = trim($parts[0]);
                $filter_names[$i] = trim($parts[1]);
                $filter_values[$i] = trim($parts[2]);
                $i = $i + 1;
            }
        @endphp
    @endisset

    @isset($actions)

        @php
            $action_names = [];
            $action_routes = [];
            $action_icons = [];
            $i = 0;
            foreach (explode(';', $actions) as $item){
                $parts = explode(',', $item);
                $action_names[$i] = trim($parts[0]);
                $action_routes[$i] = trim($parts[1]);
                $action_icons[$i] = trim($parts[2]);
                $i = $i + 1;
            }
        @endphp

    @endisset

    @isset($confirm_actions)

        @php
            $confirm_action_names = [];
            $confirm_action_routes = [];
            $confirm_action_icons = [];
            $confirm_action_message = [];
            $i = 0;
            foreach (explode(';', $confirm_actions) as $item){
                $parts = explode('|', $item);
                $confirm_action_names[$i] = trim($parts[0]);
                $confirm_action_routes[$i] = trim($parts[1]);
                $confirm_action_icons[$i] = trim($parts[2]);
                $confirm_action_message[$i] = trim($parts[3]);
                $i = $i + 1;
            }
        @endphp

    @endisset

    @isset($mass_actions)

        @php
            $mass_action_names = [];
            $mass_action_routes = [];
            $mass_action_icons = [];
            $i = 0;
            foreach (explode(';', $mass_actions) as $item){
                $parts = explode(',', $item);
                $mass_action_names[$i] = trim($parts[0]);
                $mass_action_routes[$i] = trim($parts[1]);
                $mass_action_icons[$i] = trim($parts[2]);
                $i = $i + 1;
            }
        @endphp

    @endisset

    @isset($mass_delete_route)
    <div class="modal fade" id="modal_mass_delete" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-card card">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title" id="exampleModalCenterTitle">
                            ¿Estás segur@?
                        </h4>

                        <!-- Close -->
                        <i style="cursor: pointer" class="fe fe-x-circle" data-bs-dismiss="modal" aria-label="Close"></i>
                    </div>
                    <div class="card-body">

                        @isset($mass_delete_message)
                            <p> {{$mass_delete_message}} </p>
                        @else
                            <p> Los elementos seleccionados serán borrados en masa. </p>
                        @endisset


                        <p>
                            <b>Esta acción es muy destructiva y no se puede deshacer.</b>
                        </p>

                        <form method="post" style="display:inline" action="{{route("$mass_delete_route",\Instantiation::instance())}}">
                            @csrf

                            <input type="hidden" class="items_selected" name="items_selected" value="">

                            <button type="submit" class="btn btn-danger">
                                <i class="fe fe-trash-2"></i> Eliminar seleccionados
                            </button>

                        </form>

                        <button class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">
                            Cancelar
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endisset
    <div class="card"
         data-list='{
     "valueNames": [
        @foreach($column_values as $value)


            @if($loop->last)
            "item-{{$value}}"
            @else
            "item-{{$value}}",
            @endif

        @endforeach],

        @isset($disable_pagination)
        "page": "1000000000000000000000000000000000000000",
        @else
        "page": {{request()->query('pagination') ?? '5'}},
        @endisset


     "pagination": {
         "paginationClass": "list-pagination"
     }}'
         id="dataList">


        @php

            $disabled_card_header = isset($disable_search) && isset($disable_pagination) && !isset($filters);

        @endphp

        <div class="card-header {{$disabled_card_header ? 'd-none' : ''}}">
            <div class="row align-items-center">

                @isset($disable_search)
                    <div class="col">
                    </div>
                @else
                    <div class="col">

                        <!-- Form -->
                        <form>
                            <div class="input-group input-group-flush input-group-merge input-group-reverse">
                                <input class="form-control list-search" type="search" placeholder="Buscar">
                                <span class="input-group-text">
                              <i class="fe fe-search"></i>
                            </span>
                            </div>
                        </form>

                    </div>
                @endisset



                @isset($disable_pagination)

                @else
                    <div class="col-auto me-n3">

                        <!-- Select -->
                        <form href="{{request()->url()}}">
                            <select id="pagination" class="form-select form-select-sm form-control-flush"
                                    data-choices='{"searchEnabled": false}'>
                                <option value="5" {{request()->query('pagination') == '5' ? 'selected' : ''}}>5 por página</option>
                                <option value="10" {{request()->query('pagination') == '10' ? 'selected' : ''}}>10 por página</option>
                                <option value="25" {{request()->query('pagination') == '25' ? 'selected' : ''}}>25 por página</option>
                                <option value="50" {{request()->query('pagination') == '50' ? 'selected' : ''}}>50 por página</option>
                                <option value="100" {{request()->query('pagination') == '100' ? 'selected' : ''}}>100 por página</option>
                            </select>
                        </form>

                    </div>
                @endisset



                @isset($filters)

                    <div class="col-auto">

                        <!-- Dropdown -->
                        <div class="dropdown">

                            <!-- Toggle -->
                            <button class="btn btn-sm btn-white" type="button" data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">

                                @php

                                    $filter_count = 0;

                                    foreach($filter_names as $filter_name){
                                        if(!empty(request()->query($filter_name))){
                                            if(strcmp(request()->query($filter_name),"*") !== 0){
                                                $filter_count++;
                                            }
                                        }
                                    }

                                @endphp

                                <i class="fe fe-sliders me-1"></i> Filtrar <span class="badge bg-primary @if($filter_count === 0) d-none @endif ms-1">{{$filter_count}}</span>
                            </button>

                            <!-- Menu -->
                            <form class="dropdown-menu dropdown-menu-end dropdown-menu-card">
                                <div class="card-header">

                                    <!-- Title -->
                                    <h4 class="card-header-title">
                                        Filtros disponibles
                                    </h4>

                                    <!-- Link -->
                                    <a class="btn btn-sm btn-link text-reset" type="reset" href="{{request()->url()}}">
                                        <small>Limpiar filtros</small>
                                    </a>

                                </div>
                                <div class="card-body">

                                    <!-- List group -->
                                    <div class="list-group list-group-flush mt-n4 mb-4">

                                        @foreach($filter_fields as $fields)

                                            <div class="list-group-item">
                                                <div class="row">
                                                    <div class="col">

                                                        <!-- Text -->
                                                        <small>{{$filter_fields[$loop->index]}}</small>

                                                    </div>
                                                    <div class="col-auto">

                                                        <!-- Select -->
                                                        <select class="form-select form-select-sm" name="{{$filter_names[$loop->index]}}"
                                                                data-choices='{"searchEnabled": false}'>
                                                            <option value="*" >Cualquiera</option>

                                                            @php

                                                                foreach (explode(':', $filter_values[$loop->index]) as $item){


                                                                    if(strcmp(trim(request()->query($filter_names[$loop->index])), trim($item)) === 0){
                                                                        echo '<option value="'. $item .'" selected>'. $item .'</option>';
                                                                    }else {
                                                                        echo '<option value="'. $item .'">'. $item .'</option>';
                                                                    }

                                                                }

                                                            @endphp

                                                        </select>

                                                    </div>
                                                </div> <!-- / .row -->
                                            </div>

                                        @endforeach


                                    </div>

                                    <!-- Button -->
                                    <button class="btn w-100 btn-primary" type="submit">
                                        Aplicar filtros
                                    </button>

                                </div>
                            </form>

                        </div>

                    </div>

                @endisset

            </div> <!-- / .row -->
        </div>



        <div class="table-responsive">
            <table class="table table-sm table-hover table-nowrap card-table">

                {{-- COLUMNS --}}

                <thead>
                <tr>

                    @isset($disable_selection)

                    @else
                        <th>

                            <!-- Checkbox -->
                            <div class="form-check mb-n2">
                                <input class="form-check-input list-checkbox-all" id="listCheckboxAll" onclick="select_all()" type="checkbox">
                                <label class="form-check-label" for="listCheckboxAll"></label>
                            </div>

                        </th>
                    @endisset



                    @foreach($column_values as $field)

                        @if($loop->last)
                            <th colspan="2">
                        @else
                            <th>
                                @endif

                                <a class="list-sort text-muted" data-sort="item-{{$field}}" href="#">{{$column_fields[$loop->index]}}</a>
                            </th>

                            @endforeach

                </tr>
                </thead>

                <tbody class="list fs-base">

                @foreach ($data_array as $item)

                    @isset($confirm_actions)

                    @foreach($confirm_action_names as $action)

                        <div class="modal fade" id="modal_confirm_{{$confirm_action_names[$loop->index]}}_{{$item['id']}}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-left modal-dialog-centered"  role="document">
                                <div class="modal-content">
                                    <div class="modal-card card">
                                        <div class="card-header">

                                            <!-- Title -->
                                            <h4 class="card-header-title" id="exampleModalCenterTitle">
                                                ¿Estás segur@?
                                            </h4>

                                            <!-- Close -->
                                            <i style="cursor: pointer" class="fe fe-x-circle" data-bs-dismiss="modal" aria-label="Close"></i>
                                        </div>
                                        <div class="card-body">

                                            <p>
                                            {{$confirm_action_message[$loop->index]}}
                                            </p>
                                            <p>
                                                <b>¿Deseas continuar?.</b>
                                            </p>

                                            @php

                                                $route = $confirm_action_routes[$loop->index];

                                            @endphp

                                            <form method="post" action="{{route("$route",[
                                                                'instance' => \Instantiation::instance()])}}">
                                                @csrf

                                                <input type="hidden" name="_id" value="{{$item["id"]}}">

                                                <button type="submit" class="btn btn-warning">
                                                    <i class="{{$confirm_action_icons[$loop->index]}}"></i> {{$confirm_action_names[$loop->index]}}
                                                </button>


                                            </form>

                                            <button class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">
                                                Cancelar
                                            </button>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach

                    @endisset

                    @isset($delete_item_route)

                        <div class="modal fade" id="modal_item_{{$item['id']}}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-card card">
                                        <div class="card-header">

                                            <!-- Title -->
                                            <h4 class="card-header-title" id="exampleModalCenterTitle">
                                                ¿Estás segur@?
                                            </h4>

                                            <!-- Close -->
                                            <i style="cursor: pointer" class="fe fe-x-circle" data-bs-dismiss="modal" aria-label="Close"></i>
                                        </div>
                                        <div class="card-body">


                                            @isset($delete_item_message)
                                                <p> {{$delete_item_message}} </p>
                                            @else
                                                <p> Este elemento se eliminará del sistema. </p>
                                            @endisset


                                            <p>
                                                <b>Esta acción no se puede deshacer.</b>
                                            </p>

                                            <form method="post" action="{{route("$delete_item_route",\Instantiation::instance())}}">
                                                @csrf

                                                <input type="hidden" name="_id" value="{{$item["id"]}}">

                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fe fe-trash"></i> Eliminar
                                                </button>


                                            </form>

                                            <button class="btn btn-light" data-bs-dismiss="modal" aria-label="Close">
                                                Cancelar
                                            </button>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endisset

                    <tr>

                        @isset($disable_selection)

                        @else
                            <td>

                                <!-- Checkbox -->
                                <div class="form-check">
                                    <input class="form-check-input list-checkbox" onclick="item_selected({{$item['id']}})" type="checkbox" id="item_{{$item['id']}}">
                                    <label class="form-check-label" for="item_{{$item['id']}}"></label>
                                </div>

                            </td>
                        @endisset

                        @foreach($column_values as $value)
                            <td>

                            <span class="item-{{$value}}">

                                @php
                                    $customization = $customizations[$loop->index];
                                @endphp


                                @if(empty($item[$value]))

                                    {{-- Write default value --}}
                                    @isset($customization['default'])
                                        {{$customization['default']}}
                                    @endisset

                                @else

                                    @if(count($customization) == 0)
                                        {{$item[$value]}}
                                    @else

                                        {{-- Iterations over types --}}
                                        @isset($customization['type'])

                                            {{-- Date and times --}}
                                            @if($customization['type'] === 'datetime')
                                                {{ \Carbon\Carbon::parse($item[$value])->format('d/m/Y H:i')}}
                                            @endif

                                            @if($customization['type'] === 'date')
                                                {{ \Carbon\Carbon::parse($item[$value])->format('d/m/Y')}}
                                            @endif

                                            @if($customization['type'] === 'time')
                                                {{ \Carbon\Carbon::parse($item[$value])->format('H:i')}}
                                            @endif

                                            @if($customization['type'] === 'ago')
                                                {{\Carbon\Carbon::parse($item[$value])->diffForHumans()}}
                                            @endif

                                            {{-- Email --}}
                                            @if($customization['type'] === 'email')
                                                <a class="text-reset" href="mailto:{{$item[$value]}}">
                                                    {{$item[$value]}}
                                                </a>
                                            @endif

                                            {{-- Badge --}}
                                            @if($customization['type'] === 'badge')
                                                <span class="badge bg-{{$customization['bg'] ?? 'primary'}}-soft">
                                                    {{$item[$value]}}
                                                </span>
                                            @endif

                                            {{-- HREF --}}
                                            @if($customization['type'] === 'href')
                                                <a class="text-reset" href="{{route($customization['route'], ['id' => $item['id'], 'instance' => \Instantiation::instance()])}}">
                                                    {{$item[$value]}}
                                                </a>
                                            @endif

                                        @endisset

                                        {{-- Iterations over text --}}
                                        @isset($customization['limit'])
                                            {{Str::limit($item[$value], $customization['limit'])}}
                                        @endisset

                                        @php
                                            if(!isset($customization['type']) && !isset($customization['limit'])){
                                                echo $item[$value];
                                            }
                                        @endphp


                                    @endif


                                @endif



                            </span>

                            </td>
                        @endforeach

                        @php

                            $disabled_dropdown_menu = !isset($edit_item_route) && !isset($delete_item_route) && !isset($actions);

                        @endphp

                        @if(!$disabled_dropdown_menu)
                            <td class="text-end">

                                @isset($actions)

                                    @foreach($action_names as $action)
                                        <a class="btn btn-outline-primary btn-sm" href="{{route($action_routes[$loop->index],['instance' => \Instantiation::instance(), 'id' => $item['id']])}}">
                                            <i class="{{$action_icons[$loop->index]}}"></i> {{$action_names[$loop->index]}}
                                        </a>
                                    @endforeach

                                @endisset

                                @isset($confirm_actions)

                                @foreach($confirm_action_names as $action)
                                    <a class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modal_confirm_{{$confirm_action_names[$loop->index]}}_{{$item['id']}}">
                                        <i class="{{$confirm_action_icons[$loop->index]}}"></i> {{$confirm_action_names[$loop->index]}}
                                    </a>

                                @endforeach

                                @endisset

                                @isset($edit_item_route)
                                    <a class="btn btn-outline-primary btn-sm" href="{{route("$edit_item_route",['instance' => \Instantiation::instance(), 'id' => $item['id']])}}">
                                        <i class="fe fe-edit"></i>
                                    </a>
                                @endisset

                                @isset($delete_item_route)
                                    <a class="btn btn-outline-danger btn-sm"  data-bs-toggle="modal" data-bs-target="#modal_item_{{$item['id']}}" href="#">
                                        <i class="fe fe-trash"></i>
                                    </a>
                                @endisset

                                {{--
                                <!-- Dropdown -->
                                <div class="dropdown">
                                    <a class="dropdown-ellipses dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                       aria-haspopup="true" aria-expanded="false">
                                        <i class="fe fe-more-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">

                                        @isset($edit_item_route)
                                            <a href="{{route("$edit_item_route",['instance' => \Instantiation::instance(), 'id' => $item['id']])}}" class="dropdown-item">
                                                Editar
                                            </a>
                                        @endisset

                                        @isset($delete_item_route)
                                            <a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#modal_item_{{$item['id']}}" href="#">
                                                Eliminar
                                            </a>
                                        @endisset

                                    </div>
                                </div>
                                --}}


                            </td>
                        @endif


                    </tr>

                @endforeach

                </tbody>
            </table>
        </div>

        @isset($disable_pagination)
            <div class="card-footer d-flex justify-content-between d-none">

                <!-- Pagination (prev) -->
                <ul class="list-pagination-prev pagination pagination-tabs card-pagination">
                    <li class="page-item">
                        <a class="page-link ps-0 pe-4 border-end" href="#">
                            <i class="fe fe-arrow-left me-1"></i> Ant
                        </a>
                    </li>
                </ul>

                <!-- Pagination -->
                <ul class="list-pagination pagination pagination-tabs card-pagination"></ul>

                <!-- Pagination (next) -->
                <ul class="list-pagination-next pagination pagination-tabs card-pagination">
                    <li class="page-item">
                        <a class="page-link ps-4 pe-0 border-start" href="#">
                            Sig <i class="fe fe-arrow-right ms-1"></i>
                        </a>
                    </li>
                </ul>

            </div>

            <!-- Alert -->
            <div class="list-alert alert alert-dark alert-dismissible border fade" role="alert">


                <!-- Content -->
                <div class="row align-items-center">
                    <div class="col">

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input" id="listAlertCheckbox" type="checkbox" checked disabled>
                            <label class="form-check-label text-white" for="listAlertCheckbox">
                                <span class="list-alert-count">0</span> seleccionado(s)
                            </label>
                        </div>

                    </div>
                    <div class="col-auto me-n3">



                        @isset($mass_delete_route)
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modal_mass_delete" >
                                <i class="fe fe-trash-2"></i> Borrado masivo
                            </button>
                        @endisset

                    </div>
                </div> <!-- / .row -->

                <!-- Close -->
                <button type="button" onclick="unselected_all_items();" class="list-alert-close btn-close" aria-label="Close"></button>


            </div>
        @else
            <div class="card-footer d-flex justify-content-between">

                <!-- Pagination (prev) -->
                <ul class="list-pagination-prev pagination pagination-tabs card-pagination">
                    <li class="page-item">
                        <a class="page-link ps-0 pe-4 border-end" href="#">
                            <i class="fe fe-arrow-left me-1"></i> Ant
                        </a>
                    </li>
                </ul>

                <!-- Pagination -->
                <ul class="list-pagination pagination pagination-tabs card-pagination"></ul>

                <!-- Pagination (next) -->
                <ul class="list-pagination-next pagination pagination-tabs card-pagination">
                    <li class="page-item">
                        <a class="page-link ps-4 pe-0 border-start" href="#">
                            Sig <i class="fe fe-arrow-right ms-1"></i>
                        </a>
                    </li>
                </ul>

                <!-- Alert -->
                <div class="list-alert alert alert-dark alert-dismissible border fade" role="alert">
                    <!-- Content -->
                    <div class="row align-items-center">
                        <div class="col">

                            <!-- Checkbox -->
                            <div class="form-check">
                                <input class="form-check-input" id="listAlertCheckbox" type="checkbox" checked disabled>
                                <label class="form-check-label text-white" for="listAlertCheckbox">
                                    <span class="list-alert-count">0</span> seleccionado(s)
                                </label>
                            </div>

                        </div>
                        <div class="col-auto me-n3">

                            @isset($mass_actions)

                                @foreach($mass_actions as $mass_action)

                                    <form method="post" style="display:inline" action="{{route($mass_action_routes[$loop->index],\Instantiation::instance())}}">
                                        @csrf

                                        <input type="hidden" class="items_selected" name="items_selected" value="">

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="{{$mass_action_icons[$loop->index]}}"></i> {{$mass_action_names[$loop->index]}}
                                        </button>

                                    </form>
                                @endforeach

                            @endisset

                            @isset($mass_delete_route)
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modal_mass_delete" >
                                    <i class="fe fe-trash-2"></i> Borrado masivo
                                </button>
                            @endisset

                        </div>
                    </div> <!-- / .row -->

                    <!-- Close -->
                    <button type="button" onclick="unselected_all_items();" class="list-alert-close btn-close" aria-label="Close"></button>


                </div>
            </div>
        @endisset

    </div>

    @push('scripts')

        <script>

            let items_selected = [];

            function item_selected(id)
            {

                // if item is already in array
                if($.inArray(id, items_selected) !== -1) {
                    items_selected = $.grep(items_selected, function(value) {
                        return value != id;
                    });
                    selected_all = false;
                } else {
                    items_selected.push(id);
                }

                console.log(items_selected);
                update_input_items_selected();

            }

            let items_id = [@foreach ($data_array as $item){{$item['id']}}@if(!$loop->last), @endif @endforeach]

            let selected_all = false;
            function select_all()
            {
                if(!selected_all) {
                    items_selected = items_id;
                    selected_all = true;
                } else {
                    items_selected = [];
                    selected_all = false;
                }

                console.log(items_selected);
                update_input_items_selected();
            }

            function unselected_all_items()
            {
                items_selected = [];
                selected_all = false;
                console.log(items_selected);
                update_input_items_selected();
            }

            function update_input_items_selected()
            {
                $('.items_selected').val(items_selected);
            }

        </script>

        <script type="module">



            $('#pagination').change(function(){
                let url = "{{request()->fullUrl()}}";
                url = url.replaceAll("&amp;", "&");

                @if(!empty(request()->getQueryString()))
                    url = url + "&pagination=";
                @else
                    url = url + "?pagination=";
                @endif

                    let sel = $(this).val();

                var url_on_change = new URL(url);
                url_on_change.searchParams.set("pagination", sel);
                var newUrl = url_on_change.href;

                $(location).attr('href',newUrl);

            });

        </script>
    @endpush

@endif

@if(count($data_array) === 0 and count(request()->query()) === 0 )

    <div class="card">
        <div class="card-body">

            <!-- Heading -->
            <h3 class="card-title">¡Vaya!</h3>

            <!-- Text -->
            <p class="card-text">
                @isset($create_item_message)
                    {{$create_item_message}}
                @else

                    No se ha encontrado ningún resultado.

                @endisset
            </p>

            @isset($create_item_route)
                <a href="{{route("$create_item_route", \Instantiation::instance())}}" class="btn btn-primary">
                    ¡Vamos!
                </a>
            @endisset

        </div>
    </div>

@endif

