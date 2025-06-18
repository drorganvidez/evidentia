@isset($edit)
    @if ($edit == true)
        <input type="hidden" name="_id" value="{{ $id }}" />
    @endif
@endisset
