<div class="alert alert-info">

    <i class="fas fa-bell"></i>&nbsp;

    @if($datetime == null)
        Sin límite programado de tiempo
    @else

        @if(\Carbon\Carbon::create($datetime)->lt(\Carbon\Carbon::now()))
            Tiempo finalizado
        @else
            Quedan {{\Carbon\Carbon::create($datetime)->diff(\Carbon\Carbon::now(),false)->format('%d días, %h horas y %i minutos')}}
        @endif

    @endif
</div>




