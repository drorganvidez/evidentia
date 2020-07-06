<span class="badge badge-dark">
    {!!$meeting->comittee->icon!!}

    {{$meeting->comittee->name}}

    @if(isset($meeting->comittee->subcomittee))
        / {{$meeting->comittee->subcomittee}}
    @endif
</span>
