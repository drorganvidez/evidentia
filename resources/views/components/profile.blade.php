<div class="card card-widget widget-user shadow-lg">
    <!-- Add the bg color to the header using any of the bg-* classes -->
    <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}) left">
        <h3 class="widget-user-username text-left">{!! $user->name!!} {!!  $user->surname!!}</h3>

        <h5 class="widget-user-desc text-left">
            @foreach($user->roles as $role)
                {{$role->slug}}
            @endforeach
        </h5>

    </div>
    <div class="widget-user-image">
        <img class="img-circle" src="{{route('avatar',['instance' => \Instantiation::instance(), 'id' => $user->id])}}"
             alt="User profile picture">
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="widget-user-desc text-left">
                    <x-participation :user="$user"/>
                </h5>
            </div>
            <div class="col-sm-12">
                <div class="description-block mt-0">
                    <h5 class="description-header" style="font-size: 30px">X</h5>
                    <span class="description-text" style="font-size: 20px">horas computadas</span>
                </div>
            </div>
            <div class="col-sm-3 border-right">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">X</h5>
                    <span class="description-text" style="font-size: 10px">en evidencias</span>
                </div>
            </div>
            <div class="col-sm-3 border-right">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">X</h5>
                    <span class="description-text" style="font-size: 10px">en reuniones</span>
                </div>
            </div>
            <div class="col-sm-3 border-right">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">X</h5>
                    <span class="description-text" style="font-size: 10px">en eventos</span>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">X</h5>
                    <span class="description-text" style="font-size: 10px">bonificadas</span>
                </div>
            </div>
            <div class="col-lg-12">

                <p>{!! $user->biography !!}</p>
            </div>
        </div>

        <!-- /.row -->
    </div>
</div>
