
@if($incidence->status == "PENDING")

    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-gray-dark" data-toggle="tooltip" data-placement="right" title="Pendiente de revisión" role="progressbar" aria-volumenow="40" aria-volumemin="0" aria-volumemax="100" style="width: 40%">
        </div>
    </div>

@endif

@if($incidence->status == "INREVIEW")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-info" data-toggle="tooltip" data-placement="right" title="En revisión" role="progressbar" aria-volumenow="70" aria-volumemin="0" aria-volumemax="100" style="width: 70%">
        </div>
    </div>
@endif

@if($incidence->status == "CLOSED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-success" role="progressbar" data-toggle="tooltip" data-placement="right" title="Cerrada: {{$incidence->close_reason}}" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
@endif
