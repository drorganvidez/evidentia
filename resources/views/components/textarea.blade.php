@props(['type' => '', 'type' => 'text'])
@props(['col' => '', 'type' => 'text'])
@props(['attr' => '', 'type' => 'text'])
@props(['label' => '', 'type' => 'text'])
@props(['placeholder' => '', 'type' => 'text'])
@props(['value' => '', 'type' => 'text'])
@props(['required' => '', 'type' => 'text'])
@props(['edit' => '', 'type' => 'text'])
@props(['disabled' => '', 'type' => 'text'])
@props(['step' => '', 'type' => 'text'])
@props(['description' => '', 'type' => 'text'])
@props(['class' => '', 'type' => 'text'])

<div class="form-group col-md-{{ $col }}">
    <label for="{{ $attr }}">{{ $label }}</label>
    <textarea id="summernote" type="{{ $type }}"
        class="textarea form-control {{ $class }}
           @error($attr) is-invalid @enderror"
        name="{{ $attr }}" required
        @if ($edit == true) @if ($disabled == true)
           disabled @endif @endif>

        @if (old($attr))
            {{ old($attr) }}
@else
{!! $value !!}
        @endif

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
