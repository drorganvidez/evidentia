<div class="form-group col-md-{{ $col }}">
    <label for="{{ $attr }}">{{ $label }}</label>
    <input id="{{ $attr }}" type="{{ $type }}" class="form-control" placeholder="{{ $placeholder }}" @error($attr) is-invalid @enderror name="{{ $attr }}" value="{{ old($attr) }}" required autocomplete="{{ $attr }}" autofocus>
    <small class="form-text text-muted">{{ $description }}</small>

    @error($attr)
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
