@if(!$user->hasRole('LECTURE'))
<h6 class="text-center">
    <span class="badge badge-pill badge-default">

        @if ($user->participation == "ORGANIZATION")
            ORGANIZACIÓN
        @endif

        @if ($user->participation == "INTERMEDIATE")
            INTERMEDIO
        @endif

        @if ($user->participation == "ASSISTANCE")
            ASISTENCIA
        @endif
    </span>
</h6>
@endif


