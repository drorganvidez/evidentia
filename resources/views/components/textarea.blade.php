
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

        @if(old("$name"))
            @php
                $value = old("$name")
            @endphp
        @endif

        <div class="form-control {{$is_invalid}} d-none">
        </div>

        <div id="editor" data-quill>{!! $value !!}</div>



        @error("$name")
        <div class="invalid-feedback">
            {{$message}}
        </div>
        @enderror

        <input type='hidden' name='{{$name}}' id='hidden_input'/>

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

@push('scripts')

    @error("$name")
        <script>
            $('.ql-editor').css('border', '1px solid red');
        </script>
    @enderror

    @if(!empty($value))

        <script>
            $('#hidden_input').val("{!! $value !!}");
        </script>

    @endif

    <script>

        $('.ql-editor').bind('DOMSubtreeModified', function(){
            let data = $('.ql-editor').html();
            data = data.replaceAll('"', "'");
            $('#hidden_input').val(data);
        });

    </script>

@endpush

