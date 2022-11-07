<a class="btn btn-primary btn-sm" href="{{route('coordinator.incidence.view',['instance' => \Instantiation::instance(), 'id' => $incidence->id])}}">
    <i class="fas fa-eye"></i>
    <span class="d-none d-sm-none d-md-none d-lg-inline"></span>
</a>

<x-incidencemanagecoordinator :instance="\Instantiation::instance()" :incidence="$incidence" />
