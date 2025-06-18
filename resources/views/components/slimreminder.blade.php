<div class="alert alert-light mb-0">

    <i class="fas fa-bell"></i>&nbsp;

    @if ($datetime == null)
        Sin límite programado de tiempo
    @else
        @if (\Carbon\Carbon::create($datetime)->lt(\Carbon\Carbon::now()))
            Tiempo finalizado
        @else
            Tiempo restante:
            {{ \Carbon\Carbon::create($datetime)->diff(\Carbon\Carbon::now(), false)->format('%M meses, %d días, %h horas y %i minutos') }}
        @endif

    @endif
</div>
