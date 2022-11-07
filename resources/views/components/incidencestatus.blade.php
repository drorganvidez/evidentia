@if($incidence->status == "DRAFT")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-info" data-toggle="tooltip" data-placement="right" title="En borrador" role="progressbar" aria-volumenow="33" aria-volumemin="0" aria-volumemax="100" style="width: 33%">
        </div>
    </div>
@endif

@if($incidence->status == "PENDING")

    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-gray-dark" data-toggle="tooltip" data-placement="right" title="Pendiente de revisión" role="progressbar" aria-volumenow="66" aria-volumemin="0" aria-volumemax="100" style="width: 66%">
        </div>
    </div>

@endif

@if($incidence->status == "IN REVIEW")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-success" data-toggle="tooltip" data-placement="right" title="En revisión" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
@endif

@if($incidence->status == "CLOSED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-warning" role="progressbar" data-toggle="tooltip" data-placement="right" title="Cerrada" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
@endif

@if($incidence->status == "BIN")

@endif