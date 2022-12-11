@if($evidence->status == "DRAFT")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-info" data-toggle="tooltip" data-placement="right" title="En borrador" role="progressbar" aria-volumenow="33" aria-volumemin="0" aria-volumemax="100" style="width: 33%">
        </div>
    </div>
@endif

@if($evidence->status == "PENDING")

    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-gray-dark" data-toggle="tooltip" data-placement="right" title="Pendiente de revisión" role="progressbar" aria-volumenow="66" aria-volumemin="0" aria-volumemax="100" style="width: 66%">
        </div>
    </div>

@endif

@if($evidence->status == "ACCEPTED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-success" data-toggle="tooltip" data-placement="right" title="Aceptada" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
@endif

@if($evidence->status == "REJECTED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-danger" role="progressbar" data-toggle="tooltip" data-placement="right" title="Rechazada: {{$evidence->reason_rejection?->reason}}" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
@endif

@if($evidence->status == "BIN")

@endif


