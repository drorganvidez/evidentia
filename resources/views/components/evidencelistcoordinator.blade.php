<div class="row">

    <div class="col-lg-12">
        <a href="{{ route('coordinator.evidence.list.all') }}"
            class=" {{ Route::currentRouteName() == 'coordinator.evidence.list.all' ? 'text-primary' : 'text-secondary' }} "><i
                class="fas fa-clipboard-check"></i> &nbsp;<span
                class="d-none d-sm-none d-md-inline d-lg-inline">Todas</span>
            ({{ Auth::user()->coordinator->committee->evidencesNotDraft()->count() }})</a>
        · <a href="{{ route('coordinator.evidence.list.pending') }}"
            class=" {{ Route::currentRouteName() == 'coordinator.evidence.list.pending' ? 'text-primary' : 'text-secondary' }} "><i
                class="fas fa-clock"></i> &nbsp;<span class="d-none d-sm-none d-md-inline d-lg-inline">Pendientes</span>
            ({{ Auth::user()->coordinator->committee->evidencesPending()->count() }})</a>
        · <a href="{{ route('coordinator.evidence.list.accepted') }}"
            class=" {{ Route::currentRouteName() == 'coordinator.evidence.list.accepted' ? 'text-primary' : 'text-secondary' }} "><i
                class="far fa-thumbs-up"></i> &nbsp;<span
                class="d-none d-sm-none d-md-inline d-lg-inline">Aceptadas</span>
            ({{ Auth::user()->coordinator->committee->evidencesAccepted()->count() }})</a>
        · <a href="{{ route('coordinator.evidence.list.rejected') }}"
            class=" {{ Route::currentRouteName() == 'coordinator.evidence.list.rejected' ? 'text-primary' : 'text-secondary' }} "><i
                class="far fa-thumbs-down"></i> &nbsp;<span
                class="d-none d-sm-none d-md-inline d-lg-inline">Rechazadas</span>
            ({{ Auth::user()->coordinator->committee->evidencesRejected()->count() }})</a>

    </div>

</div>
