<div>
    <form wire:submit.prevent="upload">

        @error('files.*') <span class="error">{{ $message }}</span> @enderror

        <div class="card" data-list='{"valueNames": ["name"]}'>

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
                <div class="card-header">

                    <!-- Form -->
                    <form>
                        <div class="input-group input-group-flush input-group-merge input-group-reverse">

                            <!-- Input -->
                            <input class="form-control list-search" type="search" placeholder="Buscar en tus archivos">

                            <!-- Prepend -->
                            <div class="input-group-text">
                                <span class="fe fe-search"></span>
                            </div>

                        </div>
                    </form>

                    <!-- Button -->
                    <a href="#!" class="btn btn-outline-danger btn-sm">
                        <i class="fe fe-trash-2"></i> Eliminar todos
                    </a>

                </div>

                <div class="card-body">

                    <!-- List -->
                    <ul class="list-group list-group-lg list-group-flush list my-n4">

                        @foreach($proofs as $proof)
                            <li class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">

                                        <a href="#!" class="avatar avatar-lg">
                                            <span class="avatar-title rounded bg-white text-secondary">

                                                @php

                                                    $type = $proof->file->type;
                                                    $images = array("png", "jpg", "jpeg", "svg");
                                                    $folders = array("zip", "rar", "tar.gz");

                                                @endphp

                                                @if(in_array($type, $images))

                                                    <span class="fe fe-image"></span>

                                                @elseif(in_array($type, $folders))

                                                    <span class="fe fe-folder"></span>

                                                @endif


                                            </span>
                                        </a>

                                    </div>
                                    <div class="col ms-n2">

                                        <!-- Title -->
                                        <h4 class="mb-1 name">
                                            <a href="#!">

                                                {{\Illuminate\Support\Str::limit($proof->file->name, 30)}}

                                            </a>
                                        </h4>

                                        <!-- Size -->
                                        <p class="card-text small text-muted mb-1">
                                            {{$proof->file->sizeForHuman()}}
                                        </p>

                                    </div>
                                    <div class="col-auto">

                                        <!-- Button -->
                                        <a href="#!" class="btn btn-sm btn-white d-none d-md-inline-block">
                                            <i class="fe fe-download"></i> Descargar
                                        </a>

                                    </div>
                                    <div class="col-auto">

                                        <!-- Dropdown -->
                                        <div class="dropdown">

                                            <!-- Toggle -->
                                            <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>

                                            <!-- Menu -->
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="#!" class="dropdown-item">
                                                    Action
                                                </a>
                                                <a href="#!" class="dropdown-item">
                                                    Another action
                                                </a>
                                                <a href="#!" class="dropdown-item">
                                                    Something else here
                                                </a>
                                            </div>

                                        </div>

                                    </div>
                                </div> <!-- / .row -->
                            </li>
                        @endforeach

                    </ul>

                </div>
            @endif


        </div>




    </form>
</div>