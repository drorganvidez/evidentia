<div class="form-group col-md-{{ $col }}">
    <label for="{{ $attr }}">{{ $label }}</label>
    <textarea type="{{ $type }}" class=" form-control {{ $class }}
    @error($attr) is-invalid @enderror"
              name="{{ $attr }}"

              required

              style="min-height:300px;"
    ></textarea>

    <small class="form-text text-muted">{{ $description }}</small>

    @error($attr)
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

