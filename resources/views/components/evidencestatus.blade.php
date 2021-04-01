@if($evidence->status == "DRAFT")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-info" role="progressbar" aria-volumenow="33" aria-volumemin="0" aria-volumemax="100" style="width: 33%">
        </div>
    </div>
@endif

@if($evidence->status == "PENDING")

    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-gray-dark" role="progressbar" aria-volumenow="66" aria-volumemin="0" aria-volumemax="100" style="width: 66%">
        </div>
    </div>

@endif

@if($evidence->status == "ACCEPTED")
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-success" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
@endif

@if($evidence->status == "REJECTED")
    {{$evidence->reason_rejection->reason}}
    <div class="progress progress-sm">
        <div class="progress-bar bg-gradient-warning" role="progressbar" aria-volumenow="100" aria-volumemin="0" aria-volumemax="100" style="width: 100%">
        </div>
    </div>
@endif

@if($evidence->status == "BIN")

@endif


