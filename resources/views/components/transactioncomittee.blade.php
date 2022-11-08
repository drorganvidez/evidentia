<span class="badge badge-light">
    {!!$transaction->comittee->icon!!}

    {{$transaction->comittee->name}}

    @if(isset($transaction->comittee->subcomittee))
        / {{$transaction->comittee->subcomittee}}
    @endif
</span>
