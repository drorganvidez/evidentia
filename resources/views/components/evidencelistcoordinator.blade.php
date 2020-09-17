<div class="row">
    <div class="col-lg-2 mt-1 col-3">
        <a href="{{route('coordinator.evidence.list.all',\Instantiation::instance())}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.all") ? 'btn-primary' : 'btn-secondary' }} btn-block" role="button" ><i class="fas fa-clipboard-check"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Todas</span> ({{Auth::user()->coordinator->comittee->evidences_not_draft()->count()}})</a>
    </div>
    <div class="col-lg-2 mt-1 col-3">
        <a href="{{route('coordinator.evidence.list.pending',\Instantiation::instance())}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.pending") ? 'btn-primary' : 'btn-secondary' }} btn-block" role="button" ><i class="fas fa-clock"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Pendientes</span> ({{Auth::user()->coordinator->comittee->evidences_pending()->count()}})</a>
    </div>
    <div class="col-lg-2 mt-1 col-3">
        <a href="{{route('coordinator.evidence.list.accepted',\Instantiation::instance())}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.accepted") ? 'btn-primary' : 'btn-secondary' }} btn-block"><i class="far fa-thumbs-up"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Aceptadas</span> ({{Auth::user()->coordinator->comittee->evidences_accepted()->count()}})</a>
    </div>
    <div class="col-lg-2 mt-1 col-3">
        <a href="{{route('coordinator.evidence.list.rejected',\Instantiation::instance())}}" class="btn {{ (Route::currentRouteName() == "coordinator.evidence.list.rejected") ? 'btn-primary' : 'btn-secondary' }} btn-block"><i class="far fa-thumbs-down"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Rechazadas</span> ({{Auth::user()->coordinator->comittee->evidences_rejected()->count()}})</a>
    </div>
</div>
