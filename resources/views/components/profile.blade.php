<div class="card card-primary card-outline shadow-sm">
    <div class="card-body box-profile">
        <div class="text-center">
            <img width="100" height="100" class="img-circle elevation-2"
                 src="{{route('avatar',['instance' => \Instantiation::instance(), 'id' => $user->id])}}"
                 alt="User profile picture">
        </div>

        <h3 class="profile-username text-center">{!! $user->name!!} {!!  $user->surname!!}</h3>

        <x-participation :user="$user"/>

        <p class="text-muted text-center">
            <x-roles :user="$user"/>
        </p>

        <p>{!! $user->biography !!}</p>


    </div>

</div>
