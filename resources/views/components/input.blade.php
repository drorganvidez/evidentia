@props(['id' => '', 'type' => 'text'])
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

<div class="form-group col-md-{{ $col }}">
    <label for="{{ $attr }}">{!!  $label  !!}</label>
    <input id="{{ $id }}" type="{{ $type }}"

           class="

           @if($type == "file")
               file
           @endif

           form-control"

           @if($type == "file")
           accept="image/png, image/jpeg, application/pdf, .zip,.rar,.7zip"
           multiple
           @endif

           placeholder="{{ $placeholder }}"
           @error($attr) is-invalid @enderror
           name="{{ $attr }}"

           @if(old($attr))
           value="{{ old($attr) }}"
           @else
           value="{{$value}}"
           @endif

           @if($required)
                required
           @endif

           autocomplete="{{ $attr }}" autofocus

           @if($edit == true)
                @if($disabled == true)
                    disabled
                @endif
           @endif

        @if($step != "")
            step="{{$step}}"
        @endif

           >
    <small class="form-text text-muted">{{ $description }}</small>

    @error($attr)
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
