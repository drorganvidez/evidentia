@if($status == "DRAFT")
    <div class="progress progress-sm">
        <div class="progress-bar bg-light role="progressbar" aria-volumenow="33" aria-volumemin="0" aria-volumemax="100" style="width: 33%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-light">En borrador</span>
    </small>
@endif

@if($status == "PENDING")
    <div class="progress progress-sm">
        <div class="progress-bar bg-dark" role="progressbar" aria-volumenow="66" aria-volumemin="0" aria-volumemax="100" style="width: 66%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-dark">Pendiente de revisión</span>
    </small>
@endif

@if($status == "ACCEPTED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-success" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-success">Aceptada</span>
    </small>
@endif

@if($status == "REJECTED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-danger" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
    <small>
        <span class="badge badge-pill badge-danger">Rechazada</span>
    </small>
@endif

@if($status == "BIN")

@endif


