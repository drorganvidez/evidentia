@if ($incidence->status != 'INREVIEW' and $incidence->status != 'CLOSED' and !\Carbon\Carbon::now()->gt(\Config::validate_incidences_timestamp()))
<a class="btn btn-success btn-sm" href="{{route('coordinator.incidence.review',['instance' => \Instantiation::instance(), 'id' => $incidence->id])}}">
    <i class="fa fa-search"  aria-hidden="true"></i>
    <span class="d-none d-sm-none d-md-none d-lg-inline"></span>
</a>
@endif

@if ($incidence->status != 'CLOSED' and $incidence->status == 'INREVIEW' and !\Carbon\Carbon::now()->gt(\Config::validate_incidences_timestamp()))
<a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#modal-rejected-{{$incidence->id}}">
    <i class="fa fa-times-circle "  aria-hidden="true"></i>
    <span class="d-none d-sm-none d-md-none d-lg-inline"></span>
</a>
@endif

@if (!\Carbon\Carbon::now()->gt(\Config::validate_incidences_timestamp()))
<div class="container">
    <div class="modal fade" id="modal-rejected-{{$incidence->id}}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="overflow: visible">
                <div class="modal-header">
                    <h4 class="modal-title text-wrap">Cerrar incidencia</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="{{route('coordinator.incidence.close',['instance' => \Instantiation::instance()])}}" method="POST">
                    @csrf
                    <input type="hidden" name="_id" value="{{$incidence->id}}"/>
                    <div class="modal-body text-wrap">
                        <x-textareasimple type="text" class="" col="12" attr="reasonrejection"
                                    label="Motivo de cierre"
                                    description="Escribe un motivo de por qué se cierre esta incidencia (entre 10 y 1000 caracteres)."
                        />
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-danger">
                            Cerrar incidencia
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endif
