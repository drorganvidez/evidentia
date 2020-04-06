<span class="badge badge-dark">
    {!!$evidence->comittee->icon!!}

    {{$evidence->comittee->comittee}}

    @if(isset($evidence->comittee->subcomittee))
        / {{$evidence->comittee->subcomittee}}
    @endif
</span>
