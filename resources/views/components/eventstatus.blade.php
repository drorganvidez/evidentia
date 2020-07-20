@if($event->status == "draft")
    <span class="badge badge-light">En borrador</span>
@endif

@if($event->status == "live")
    <span class="badge badge-success">Pendiente</span>
@endif

@if($event->status == "started")
    <span class="badge badge-info">En curso</span>
@endif

@if($event->status == "ended")
    <span class="badge badge-info">Finalizado</span>
@endif

@if($event->status == "completed")
    <span class="badge badge-secondary">Completado</span>
@endif

@if($event->status == "canceled")
    <span class="badge badge-danger">Cancelado</span>
@endif
