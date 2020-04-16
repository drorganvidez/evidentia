<a class="btn btn-primary btn-sm" href="{{route('evidence.view',['instance' => $instance, 'id' => $evidence->id])}}">
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

<x-buttonremove :id="$evidence->id" route="evidence.remove" title="¿Seguro?" description="Esto borrará la evidencia actual, las
ediciones anteriores <b>y todos los archivos adjuntos.</b>"/>
