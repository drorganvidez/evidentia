
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

        @if(old("$name"))
            @php
                $value = old("$name")
            @endphp
        @else
            @php
                $value = old("$name")
            @endphp
        @endif

        <div id="editor" data-quill>{!! $value !!}</div>

        @error("$name")
        error {{$message}}
        <div class="invalid-feedback">
            {{$message}}
        </div>
        @enderror

        <input type='hidden' name='{{$name}}' id='hidden_input'/>

        @push('scripts')

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

    </div>

</div>

