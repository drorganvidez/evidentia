<span class="badge badge-light">
    {!!$kanbantable->comittee->icon!!}

    {{$kanbantable->comittee->name}}

    @if(isset($kanbantable->comittee->subcomittee))
        / {{$kanbantable->comittee->subcomittee}}
    @endif
</span>