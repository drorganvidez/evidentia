@php

    $data_string = (string) $data;
    $data_string = str_replace("&quot;",'"',$data_string);
    $data_array = json_decode($data_string, true);

@endphp

@php

    if(isset($order)){
        $or = $order;
    } else {
        $or = "";
    }

@endphp

<div class="{{$col}} {{$or}}">

    <div class="form-group">

        <!-- Label -->
        @isset($description)
            @php
                $label_class = 'form-class mb-1';
            @endphp
        @else
            @php
                $label_class = "form-class mb-3"
            @endphp
        @endisset
        <label class="{{$label_class}}">

            {{$label}}

            @isset($info)
                <i style="cursor: pointer" class="fe fe-info" data-bs-toggle="modal" data-bs-target="#modal_item_{{$name}}"></i>
            @endisset

        </label>

        @isset($description)
            <!-- Description -->
            <small class="form-text text-muted">
                {{$description}}
            </small>
        @endisset

        <select class="form-select mb-3" name="{{$name}}" data-choices>

            @isset($default)
                <option selected value="">{{$default}}</option>
            @endisset

            @foreach ($data_array as $item)

                @if(old("$name"))
                    <option @if(strcmp(trim(old("$name")),$item['id']) === 0) selected @endif value="{{$item['id']}}">{{$item["$option_name"]}}</option>
                @else
                    @if(strcmp($value, "") !== 0)
                        <option @if(strcmp(trim($value),$item['id']) === 0) selected @endif value="{{$item['id']}}">{{$item["$option_name"]}}</option>
                    @else
                        <option value="{{$item['id']}}">{{$item["$option_name"]}}</option>
                    @endif
                @endif

            @endforeach

        </select>

    </div>

    @isset($info)
        <div class="modal fade" id="modal_item_{{$name}}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-card card">
                        <div class="card-header">

                            <!-- Title -->
                            <h4 class="card-header-title" id="exampleModalCenterTitle">
                                {{$label}}: para tu interés
                            </h4>

                            <!-- Close -->
                            <i style="cursor: pointer" class="fe fe-x-circle" data-bs-dismiss="modal" aria-label="Close"></i>
                        </div>
                        <div class="card-body">

                            {{$info}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset

</div>