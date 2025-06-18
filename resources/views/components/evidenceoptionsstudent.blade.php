<a class="btn btn-primary btn-sm" href="{{route('evidence.view',['id' => $evidence->id])}}">
    <i class="fas fa-eye"></i>
    <span class="d-none d-sm-none d-md-none d-lg-inline"></span>
</a>

@if($evidence->status == 'DRAFT' and !\Carbon\Carbon::now()->gt(\Config::upload_evidences_timestamp()))
<a class="btn btn-info btn-sm" href="{{route('evidence.edit',['id' => $evidence->id])}}">
    <i class="fas fa-pencil-alt">
    </i>
    <span class="d-none d-sm-none d-md-none d-lg-inline"></span>
</a>
@endif

@if($evidence->status == 'REJECTED' and !\Carbon\Carbon::now()->gt(\Config::upload_evidences_timestamp()))
    <x-buttonconfirm :id="$evidence->id" route="evidence.reedit" title="" description="Puedes volver a editar una evidencia
que ha sido rechazada. Esta pasará automáticamente a borrador y tendrá que ser validada de nuevo por tu coordinador." type="INFO" name=""/>
@endif

@if(!\Carbon\Carbon::now()->gt(\Config::upload_evidences_timestamp()))
<x-buttonconfirm :id="$evidence->id" route="evidence.remove" title="¿Seguro?" description="Esto borrará la evidencia actual, las
ediciones anteriores <b>y todos los archivos adjuntos.</b>" type="REMOVE" />
@endif


