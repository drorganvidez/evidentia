<div class="card card-widget widget-user shadow-sm">
    <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }})">
        <h5 class="widget-user-username text-left" style="font-size: 20px">{!!$user->name!!} {!!$user->surname!!}</h5>
        <h5 class="widget-user-desc text-left" style="font-size: 15px">
            @foreach($user->roles as $role)
                @if (!$loop->first)
                    -
                @endif
                {{$role->slug}}
            @endforeach
        </h5>
    </div>
    <div class="widget-user-image">
        <img class="img-circle" src="{{$user->avatar_route()}}" alt="User Avatar">
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="widget-user-desc text-left">
                    <x-participation :user="$user"></x-participation>
                </h5>
            </div>
            <div class="col-sm-12">
                <div class="description-block mt-0">
                    <h5 class="description-header" style="font-size: 30px">{{$user->total_computed_hours()}}</h5>
                    <span class="description-text" style="font-size: 20px">horas computadas</span>
                </div>
            </div>
            <div class="col-sm-3 border-right">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">{{$user->evidences_accepted_hours()}}</h5>
                    <span class="description-text" style="font-size: 10px">en evidencias</span>
                </div>
            </div>
            <div class="col-sm-3 border-right">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">{{$user->meetings_hours()}}</h5>
                    <span class="description-text" style="font-size: 10px">en reuniones</span>
                </div>
            </div>
            <div class="col-sm-3 border-right">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">{{$user->events_hours()}}</h5>
                    <span class="description-text" style="font-size: 10px">en eventos</span>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="description-block">
                    <h5 class="description-header" style="font-size: 20px">{{$user->bonus_hours()}}</h5>
                    <span class="description-text" style="font-size: 10px">bonificadas</span>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="card card-widget widget-user shadow-sm">

    <div class="widget-user-header text-white" style="background: url({{ asset('dist/img/abstract.jpg') }}); height: auto">

        <h5 class="widget-user-desc text-left" style="margin-bottom: 0px"><i class="fas fa-briefcase"></i>&nbsp;&nbsp;
            Resumen de trabajo realizado en las Jornadas Innosoft Days {{\Carbon\Carbon::now()->year}}
        </h5>

        <h5 class="widget-user-desc text-left">

        </h5>


    </div>



    <div class="card-footer" style="padding-top: 10px">
        <div class="row">

            <div class="col-lg-12">
                <span class="description-text" style="font-size: 20px; text-align: left;"></span>
                <p style="margin-bottom: 0px; text-align: justify" class="biography">

                    @if(!$user->biography)

                        <div class="callout callout-danger mt-3">
                            <h5>Ups...</h5>

                            <p>Parece que no has rellenado este apartado. ¡No lo olvides! Indica con detalle tu nivel
                                de implicación en las jornadas desde <a href="{{route('profile.view',\Instantiation::instance())}}">Mi perfil</a></p>
                        </div>

                    @endif

                    {!! $user->biography !!}
                </p>
            </div>
        </div>

    </div>
</div>

