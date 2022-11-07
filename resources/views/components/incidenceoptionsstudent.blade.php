<a class="btn btn-primary btn-sm" href="{{route('incidence.view',['instance' => \Instantiation::instance(), 'id' => $incidence->id])}}">
    <i class="fas fa-eye"></i>
    <span class="d-none d-sm-none d-md-none d-lg-inline"></span>
</a>


@if($incidence->status != 'CLOSED' and  !\Carbon\Carbon::now()->gt(\Config::upload_incidences_timestamp()))
    <x-buttonconfirm :id="$incidence->id" route="incidence.remove" title="¿Seguro?" description="Esto borrará la evidencia actual, las
    ediciones anteriores <b>y todos los archivos adjuntos.</b>" type="REMOVE" />
@endif