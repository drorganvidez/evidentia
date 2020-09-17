@if($evidence->status == "DRAFT")
    <div class="progress progress-sm">
        <div class="progress-bar bg-info progress-bar-striped" role="progressbar" aria-volumenow="33" aria-volumemin="0" aria-volumemax="100" style="width: 33%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-info">En borrador</span>
    </small>
@endif

@if($evidence->status == "PENDING")
    <div class="progress progress-sm">
        <div class="progress-bar bg-dark progress-bar-striped" role="progressbar" aria-volumenow="66" aria-volumemin="0" aria-volumemax="100" style="width: 66%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-dark">Pendiente de revisión</span>
    </small>
@endif

@if($evidence->status == "ACCEPTED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-success progress-bar-striped" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-success">Aceptada</span>
    </small>
@endif

@if($evidence->status == "REJECTED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-warning progress-bar-striped" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-warning">Rechazada</span>
    </small>
    <small>
        <span class="badge badge-pill badge-info" data-toggle="tooltip"
              data-placement="bottom" onmouseover="" style="cursor: pointer;"
              title="{{$evidence->reason_rejection->reason}}">¿Por qué?</span>
    </small>
@endif

@if($evidence->status == "BIN")

@endif


