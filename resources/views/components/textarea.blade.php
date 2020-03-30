<div class="form-group col-md-{{ $col }}">
    <label for="{{ $attr }}">{{ $label }}</label>
    <textarea id="summernote" type="{{ $type }}" class="textarea form-control {{ $class }}
           @error($attr) is-invalid @enderror"
           name="{{ $attr }}"

           @if(old($attr))
           value="{{ old($attr) }}"
           @else
           value="{{$value}}"
           @endif

           required

           @if($edit == true)
           @if($disabled == true)
           disabled
           @endif
           @endif

    >
    </textarea>

    <style>
        .note-group-select-from-files {
            display: none;
        }
    </style>

    <small class="form-text text-muted">{{ $description }}</small>

    @error($attr)
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

