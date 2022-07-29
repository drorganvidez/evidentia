<div>
    <form wire:submit.prevent="upload">

        @error('files.*') <span class="error">{{ $message }}</span> @enderror

        <div class="card" @if(count($proofs) > 0) data-list='{"valueNames": ["name"]}' @endif>

            <div class="card-header">

                <div class="row">

                    <div class="col-lg-9 col-md-12">
                        <input class="form-control form-control-sm"
                               type="file" name="attachment" id="upload{{ $iteration }}"
                               wire:model="files" wire:change="toggle_button" multiple>
                    </div>

                    <div class="col-lg-3 col-md-12">

                        @if($show_button)

                            <button class="btn btn-primary btn-sm" type="submit"><i class="fe fe-upload"></i> Subir</button>

                        @endif
                    </div>

                </div>

            </div>

            @if(count($proofs) > 0)

                <div class="card-body">

                    <!-- List -->
                    <ul class="list-group list-group-lg list-group-flush list my-n4">

                        @foreach($proofs as $proof)
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
                                    <div class="col ms-n2">

                                        <!-- Title -->
                                        <h4 class="mb-1 name">
                                            <a href="{{route('download.file', ['instance' => \Instantiation::instance(), 'file_id' => $proof->file->id])}}">

                                                {{\Illuminate\Support\Str::limit($proof->file->name, 20)}}

                                            </a>
                                        </h4>

                                        <!-- Size -->
                                        <p class="card-text small text-muted mb-1">
                                            {{$proof->file->sizeForHuman()}}
                                        </p>

                                    </div>
                                    <div class="col-auto">

                                        <!-- Button -->
                                        <a target="_blank" href="{{route('download.file', ['instance' => \Instantiation::instance(), 'file_id' => $proof->file->id])}}" class="btn btn-sm btn-white d-none d-md-inline-block">
                                            <i class="fe fe-download"></i> Descargar
                                        </a>

                                        <!-- Button -->
                                        <a href="#!" wire:click="delete_file({{ $proof->file->id }})" class="btn btn-sm btn-outline-danger d-none d-md-inline-block">
                                            <i class="fe fe-trash"></i> Eliminar
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




    </form>
</div>