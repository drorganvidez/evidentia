@php

    $data_string = (string) $data;
    $data_string = str_replace("&quot;",'"',$data_string);
    $data_array = json_decode($data_string, true);

@endphp

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

        <select class="form-select mb-3" name="{{$name}}" data-choices>

            @foreach ($data_array as $item)
                <option value="{{$item['id']}}">{{$item["$option_name"]}}</option>
            @endforeach

        </select>

    </div>

</div>