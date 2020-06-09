<a class="btn btn-primary btn-sm" href="{{route('coordinator.evidence.view',['instance' => $instance, 'id' => $evidence->id])}}">
    <i class="fas fa-eye"></i>
    Ver
</a>

<x-evidencemanagecoordinator :instance="$instance" :evidence="$evidence" />
