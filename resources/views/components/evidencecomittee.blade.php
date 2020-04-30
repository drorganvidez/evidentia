<span class="badge badge-dark">
    {!!$evidence->comittee->icon!!}

    {{$evidence->comittee->name}}

    @if(isset($evidence->comittee->subcomittee))
        / {{$evidence->comittee->subcomittee}}
    @endif
</span>
