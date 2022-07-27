
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

        <div data-quill=''>{{$evidence->description ?? ''}}</div>

    </div>

</div>

