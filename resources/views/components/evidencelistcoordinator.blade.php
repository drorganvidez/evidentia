<div class="row">

    <div class="col-lg-12">
        <a href="{{route('coordinator.evidence.list.all',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.evidence.list.all") ? 'text-primary' : 'text-secondary' }} "  ><i class="fas fa-clipboard-check"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Todas</span> ({{Auth::user()->coordinator->comittee->evidences_not_draft()->count()}})</a>
        · <a href="{{route('coordinator.evidence.list.pending',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.evidence.list.pending") ? 'text-primary' : 'text-secondary' }} "  ><i class="fas fa-clock"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Pendientes</span> ({{Auth::user()->coordinator->comittee->evidences_pending()->count()}})</a>
        · <a href="{{route('coordinator.evidence.list.accepted',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.evidence.list.accepted") ? 'text-primary' : 'text-secondary' }} "><i class="far fa-thumbs-up"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Aceptadas</span> ({{Auth::user()->coordinator->comittee->evidences_accepted()->count()}})</a>
        · <a href="{{route('coordinator.evidence.list.rejected',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.evidence.list.rejected") ? 'text-primary' : 'text-secondary' }} "><i class="far fa-thumbs-down"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Rechazadas</span> ({{Auth::user()->coordinator->comittee->evidences_rejected()->count()}})</a>

    </div>

</div>
