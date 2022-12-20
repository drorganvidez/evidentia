@php

$is_invalid = "";

@endphp

@error("$name")
    @php

        $is_invalid = " is-invalid";

    @endphp
@enderror

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

        @isset($disabled)
            @php
                $disabled = 'disabled="true"';
            @endphp
        @else
            @php
                $disabled = ""
            @endphp
        @endisset

        @isset($autofocus)
            @php
                $autofocus = 'autofocus';
            @endphp
        @else
            @php
                $autofocus = ""
            @endphp
        @endisset

        @isset($noold)
            @php
                $noold = true;
            @endphp
        @else
            @php
                $noold = false;
            @endphp
        @endisset

        @isset($type)
            @php

            @endphp
        @else
            @php
                $type = "text"
            @endphp
        @endisset

        @isset($required)
            @php
                $required = "required"
            @endphp
        @else
            @php
                $required = ""
            @endphp
        @endisset

        @php

            if(!$noold){

                if(old("$name")){
                    $val = old("$name");
                }else{
                    $val = $value;
                }

            }else {
                $val = $value;
            }

        @endphp

        <!-- Input -->
        <input type="{{$type}}" name="{{$name}}" value="{{$val}}" class="form-control{{$is_invalid}}" {{$disabled}} {{$autofocus}} {{$required}} >

        @error("$name")
        <div class="invalid-feedback">
            {{$message}}
        </div>
        @enderror
    </span>

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