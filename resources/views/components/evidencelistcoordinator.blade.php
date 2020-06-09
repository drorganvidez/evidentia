<div class="row">
    <div class="col-lg-2 mt-1">
        <a href="{{route('coordinator.evidence.list.all',$instance)}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.all") ? 'btn-primary' : 'btn-secondary' }} btn-block" role="button" ><i class="fas fa-clipboard-check"></i> &nbsp;Todas</a>
    </div>
    <div class="col-lg-2 mt-1">
        <a href="{{route('coordinator.evidence.list.pending',$instance)}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.pending") ? 'btn-primary' : 'btn-secondary' }} btn-block" role="button" ><i class="fas fa-clock"></i> &nbsp;Pendientes</a>
    </div>
    <div class="col-lg-2 mt-1">
        <a href="{{route('coordinator.evidence.list.accepted',$instance)}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.accepted") ? 'btn-primary' : 'btn-secondary' }} btn-block"><i class="far fa-thumbs-up"></i> &nbsp;Aceptadas</a>
    </div>
    <div class="col-lg-2 mt-1">
        <a href="{{route('coordinator.evidence.list.rejected',$instance)}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.rejected") ? 'btn-primary' : 'btn-secondary' }} btn-block"><i class="far fa-thumbs-down"></i> &nbsp;Rechazadas</a>
    </div>
</div>
