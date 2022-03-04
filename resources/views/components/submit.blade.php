@isset($name)
@else
    @php
        $name = "Enviar"
    @endphp
@endisset

<!-- Button -->
<button class="btn btn-primary">
    {{$name}}
</button>