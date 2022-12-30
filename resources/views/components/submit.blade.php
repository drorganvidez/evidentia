@isset($name)
@else
    @php
        $name = "Enviar"
    @endphp
@endisset

@isset($id)
    @php
        $identificator = $id
    @endphp
@else
    @php
        $identificator = ""
    @endphp
@endisset

@isset($disabled)
    @php
        $dis = "disabled"
    @endphp
@else
    @php
        $dis = ""
    @endphp
@endisset

<!-- Button -->
<button class="btn btn-primary" id="{{$identificator}}" {{$dis}}>
    {{$name}}
</button>