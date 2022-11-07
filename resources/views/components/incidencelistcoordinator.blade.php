<div class="row">

    <div class="col-lg-12">
        <a href="{{route('coordinator.incidence.list.all',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.incidence.list.all") ? 'text-primary' : 'text-secondary' }} "  ><i class="fas fa-clipboard-check"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Todas</span> ({{Auth::user()->coordinator->comittee->incidences()->count()}})</a>
        · <a href="{{route('coordinator.incidence.list.pending',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.incidence.list.pending") ? 'text-primary' : 'text-secondary' }} "  ><i class="fas fa-clipboard-check"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Nuevas</span> ({{Auth::user()->coordinator->comittee->incidences_pending()->count()}})</a>
        · <a href="{{route('coordinator.incidence.list.inreview',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.incidence.list.inreview") ? 'text-primary' : 'text-secondary' }} "  ><i class="fas fa-clock"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">En revisión</span> ({{Auth::user()->coordinator->comittee->incidences_in_review()->count()}})</a>
        · <a href="{{route('coordinator.incidence.list.closed',\Instantiation::instance())}}" class=" {{ (Route::currentRouteName() == "coordinator.incidence.list.close") ? 'text-primary' : 'text-secondary' }} "><i class="far fa-thumbs-up"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Cerradas</span> ({{Auth::user()->coordinator->comittee->incidences_closed()->count()}})</a>

    </div>

</div>
