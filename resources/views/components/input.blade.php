@php

$is_invalid = "";

@endphp

@error("$name")
@php

    $is_invalid = " is-invalid";

@endphp
@enderror

<div class="{{$col}}">

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

        @if(old("$name"))
            @php
                $value = old("$name")
            @endphp
        @endif

        <!-- Input -->
        <input type="{{$type}}" name="{{$name}}" value="{{$value}}" class="form-control{{$is_invalid}}" {{$disabled}} {{$autofocus}} {{$required}}>

        @error("$name")
        <div class="invalid-feedback">
            {{$message}}
        </div>
        @enderror
    </span>

    </div>

</div>