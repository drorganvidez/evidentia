<div class="user-panel  d-flex">
    <div class="image">
        <img src="{{asset('dist/img/user2-160x160.jpg')}}" class="img-circle elevation-2" style="margin-top: 10px"
             alt="User Image">
    </div>
    <div class="info">
        <a href="#">

            <span style="font-size: 12px; display: block; line-height : 15px;">
                {{Auth::user()->surname}}
            </span>

            <span style="font-size: 14px; display: block; line-height : 15px;">
                {{Auth::user()->name}}
            </span>

            <span style="font-size: 12px; display: block; line-height : 20px;">
                {{Auth::user()->email}}
            </span>

        </a>



            @if(Schema::hasTable('roles'))
                @foreach(Auth::user()->roles as $rol)
                    <span class="badge badge-pill badge-secondary">{{$rol->slug}}</span>
                @endforeach
            @endif



    </div>
</div>
