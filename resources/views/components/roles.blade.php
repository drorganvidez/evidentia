
@foreach($user->roles as $rol)
    <span class="badge badge-pill badge-secondary">
        {{$rol->slug}}

        @if($rol->rol == 'COORDINATOR')
            de {{$user->coordinator->committee->name}}
        @endif

        @if($rol->rol == 'SECRETARY')
            de {{$user->secretary->committee->name}}
        @endif

    </span>
@endforeach

