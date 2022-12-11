<span class="badge badge-light">
    {!!$incidence->comittee->icon!!}

    {{$incidence->comittee->name}}

    @if(isset($incidence->comittee->subcomittee))
        / {{$incidence->comittee->subcomittee}}
    @endif
</span>