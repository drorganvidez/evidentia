<div class="form-group col-md-{{ $col }}">
    <label for="{{ $attr }}">{{ $label }}</label>
    <textarea type="{{ $type }}" class=" form-control {{ $class }}
    @error($attr) is-invalid @enderror"
              name="{{ $attr }}"

              required

              @if($edit == true)
              @if($disabled == true)
              disabled
           @endif
        @endif
              style="min-height:300px;"
    >@if(old($attr)){{ old($attr) }}@else{!! $value !!}@endif</textarea>

    <small class="form-text text-muted">{{ $description }}</small>

    @error($attr)
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

