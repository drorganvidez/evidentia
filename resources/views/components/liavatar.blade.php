<div class="row" style="margin-top: 10px; margin-left: 5px; margin-bottom: 0px; padding-bottom: 0px">
    <div class="col-lg-3">

            <img src="{{asset('dist/img/user2-160x160.jpg')}}" style="width: 50px" class="img-circle" alt="User Image">

    </div>
    <div class="col-lg-9">
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
    </div>

    <div class="col-12">
        <p style="color: #c2c7d0; font-size: 12px; margin-top: 10px">
            @foreach(Auth::user()->roles as $rol)
                <span class="badge badge-pill badge-secondary">{{$rol->slug}}</span>
            @endforeach
        </p>
    </div>

</div>
