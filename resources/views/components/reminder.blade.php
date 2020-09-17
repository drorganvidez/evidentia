<div class="info-box mb-3 bg-info">
    <span class="info-box-icon"><i class="fas fa-bell"></i></span>

    <div class="info-box-content">

        @if($title != "")
            <span class="info-box-text">{{$title}}</span>
        @endif

        <span class="info-box-number">

            @if($datetime == null)
                Sin límite programado de tiempo
            @else
                Quedan

                @if(\Carbon\Carbon::create($datetime)->lt(\Carbon\Carbon::now()))
                    0 días, 0 horas y 0 minutos
                @else
                    {{\Carbon\Carbon::create($datetime)->diff(\Carbon\Carbon::now(),false)->format('%M meses, %d días, %H horas y %i minutos')}}
                @endif

            @endif



        </span>
    </div>

</div>
