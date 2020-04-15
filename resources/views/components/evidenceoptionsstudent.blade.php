<a class="btn btn-primary btn-sm" href="#">
    <i class="fas fa-eye"></i>
    Ver
</a>

@if($evidence->status == 'DRAFT')
<a class="btn btn-info btn-sm" href="{{route('evidence.edit',['instance' => $instance, 'id' => $evidence->id])}}">
    <i class="fas fa-pencil-alt">
    </i>
    Editar
</a>
@endif

<x-buttonremove :id="$evidence->id" route="evidence.remove"/>
