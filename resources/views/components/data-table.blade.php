@php

    $data_string = (string) $data;
    $data_string = str_replace("&quot;",'"',$data_string);
    $data_array = json_decode($data_string, true);

@endphp

@isset($columns)

    @php

        $column_fields = [];
        $column_values = [];
        $i = 0;
        foreach (explode(';', $columns) as $item){
            $parts = explode(',', $item);
            $column_fields[$i] = trim($parts[0]);
            $column_values[$i] = trim($parts[1]);
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

                        <input type="hidden" id="items_selected" name="items_selected" value="">

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
     "page": 5,
     "pagination": {
         "paginationClass": "list-pagination"
     }}'
     id="dataList">



    <div class="card-header">
        <div class="row align-items-center">
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
            <div class="col-auto me-n3">

                <!-- Select -->
                <form>
                    <select class="form-select form-select-sm form-control-flush"
                            data-choices='{"searchEnabled": false}'>
                        <option selected>5 por página</option>
                        <option>10 por página</option>
                        <option>Todos</option>
                    </select>
                </form>

            </div>

            @isset($filters)

            <div class="col-auto">

                <!-- Dropdown -->
                <div class="dropdown">

                    <!-- Toggle -->
                    <button class="btn btn-sm btn-white" type="button" data-bs-toggle="dropdown"
                            data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class="fe fe-sliders me-1"></i> Filtrar <span class="badge bg-primary ms-1 d-none">0</span>
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


                                                            if(request()->query($filter_names[$loop->index]) === $item){
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
                <th>

                    <!-- Checkbox -->
                    <div class="form-check mb-n2">
                        <input class="form-check-input list-checkbox-all" id="listCheckboxAll" onclick="select_all()" type="checkbox">
                        <label class="form-check-label" for="listCheckboxAll"></label>
                    </div>

                </th>

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
                    <td>

                        <!-- Checkbox -->
                        <div class="form-check">
                            <input class="form-check-input list-checkbox" onclick="item_selected({{$item['id']}})" type="checkbox" id="item_{{$item['id']}}">
                            <label class="form-check-label" for="item_{{$item['id']}}"></label>
                        </div>

                    </td>

                    @foreach($column_values as $value)
                        <td>

                            <span class="item-{{$value}}">
                                {{$item[$value]}}
                            </span>

                        </td>
                    @endforeach


                    <td class="text-end">

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



                    </td>
                </tr>

            @endforeach

            </tbody>
        </table>
    </div>



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

                    @isset($mass_delete_route)
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modal_mass_delete" >
                            <i class="fe fe-trash-2"></i> Borrado masivo
                        </button>
                    @endisset

                </div>
            </div> <!-- / .row -->

            <!-- Close -->
            <button type="button" class="list-alert-close btn-close" aria-label="Close"></button>


        </div>

    </div>
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

        function update_input_items_selected()
        {
            $('#items_selected').val(items_selected);
        }

    </script>
@endpush

<script>

</script>