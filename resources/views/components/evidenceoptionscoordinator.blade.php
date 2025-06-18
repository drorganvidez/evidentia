<a class="btn btn-primary btn-sm" href="{{ route('coordinator.evidence.view', ['id' => $evidence->id]) }}">
    <i class="fas fa-eye"></i>
    <span class="d-none d-sm-none d-md-none d-lg-inline"></span>
</a>

<x-evidencemanagecoordinator :evidence="$evidence" />
