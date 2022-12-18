<span class="badge badge-light">
    {!!$task->comittee->icon!!}

    {{$task->comittee->name}}

    @if(isset($task->comittee->subcomittee))
        / {{$task->comittee->subcomittee}}
    @endif
</span>
