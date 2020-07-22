<div class="user-panel  d-flex">
    <div class="image">
        <img width="33" height="33" src="{{Auth::user()->avatar_route()}}" class="img-circle elevation-2" style="margin: 10px 0px"
             alt="User Image">
    </div>
    <div class="info" style="margin: 6px 0px">
        <a href="#">

            <span style="font-size: 14px; display: block; line-height : 15px;">
                {!! Auth::user()->surname!!}
            </span>

            <span style="font-size: 16px; display: block; line-height : 15px;">
            {!!Auth::user()->name!!}
</span>

</a>




</div>
</div>
