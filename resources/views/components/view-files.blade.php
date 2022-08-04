<div class="card" @if(count($evidence->proofs) > 0) data-list='{"valueNames": ["name"]}' @endif>

    <div class="card-header">

        <!-- Title -->
        <h4 class="card-header-title" id="exampleModalCenterTitle">
            Archivos adjuntos
        </h4>

    </div>
    <div class="card-header">

        <!-- Form -->
        <form>
            <div class="input-group input-group-flush input-group-merge input-group-reverse">
                <input class="form-control list-search" type="search" placeholder="Buscar">
                <div class="input-group-text">
                    <span class="fe fe-search"></span>
                </div>
            </div>
        </form>

    </div>

    @if(count($evidence->proofs) > 0)

        <div class="card-body">

            <!-- List -->
            <ul class="list-group list-group-lg list-group-flush list my-n4">

                @foreach($evidence->proofs as $proof)
                    <li class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">

                                <a href="#!" style="cursor: default" class="avatar avatar-lg">
                                            <span class="avatar-title rounded bg-white text-secondary">

                                                @php

                                                    $type = $proof->file->type;

                                                    $images = array("png", "jpg", "jpeg", "svg");
                                                    $folders = array("zip", "rar", "tar.gz");
                                                    $docs = array("docs", "docx", "txt" ,"pdf");
                                                    $xls = array("xls", "xlsx");

                                                @endphp

                                                @if(in_array($type, $images))

                                                    <span class="fe fe-image"></span>

                                                @elseif(in_array($type, $folders))

                                                    <span class="fe fe-folder"></span>

                                                @elseif(in_array($type, $docs))

                                                    <span class="fe fe-file-text"></span>

                                                @elseif(in_array($type, $xls))

                                                    <span class="fe fe-pie-chart"></span>

                                                @else

                                                    <span class="fe fe-file"></span>

                                                @endif


                                            </span>
                                </a>

                            </div>
                            <div class="col w-25 ms-n2">

                                <!-- Title -->
                                <h5 class="mb-1 name">
                                    <a href="{{route('download.file', ['instance' => \Instantiation::instance(), 'file_id' => $proof->file->id])}}">

                                        {{$proof->file->name}}

                                    </a>
                                </h5>

                                <!-- Size -->
                                <p class="card-text small text-muted mb-1">
                                    {{$proof->file->sizeForHuman()}}
                                </p>

                            </div>
                            <div class="col-auto text-end d-none d-lg-block ">

                                <!-- Button -->
                                <a href="{{route('download.file', ['instance' => \Instantiation::instance(), 'file_id' => $proof->file->id])}}" class="btn btn-sm btn-white d-md-inline-block">
                                    <i class="fe fe-download"></i> Descargar
                                </a>

                            </div>

                        </div> <!-- / .row -->
                    </li>
                @endforeach

            </ul>

        </div>
    @else

        <div class="card-body">
            Sin archivos
        </div>

    @endif

</div>