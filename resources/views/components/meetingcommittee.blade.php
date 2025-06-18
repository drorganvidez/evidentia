<span class="badge badge-dark">
    {!! $meeting->committee->icon !!}

    {{ $meeting->committee->name }}

    @if (isset($meeting->committee->subcommittee))
        / {{ $meeting->committee->subcommittee }}
    @endif
</span>
