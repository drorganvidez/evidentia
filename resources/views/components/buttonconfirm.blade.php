<a class="btn

    @if($type == "REMOVE")
        btn-danger
    @elseif($type == "INFO")
        btn-info
    @endif


 btn-sm" href="#" data-toggle="modal" data-target="#modal-confirm-{{$type}}-{{$id}}">


    @if($type == "REMOVE")
        <i class="fas fa-trash"></i> <span class="d-none d-sm-none d-md-none d-lg-inline">{{ $name ?? '' }} </span>
    @elseif($type == "INFO")
        <i class="fas fa-info"></i> <span class="d-none d-sm-none d-md-none d-lg-inline">{{ $name ?? '' }}</span>
    @endif

</a>

<div class="container" style="display: inline; padding: 0px">
    <div class="modal fade" id="modal-confirm-{{$type}}-{{$id}}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="overflow: visible">
                <div class="modal-header">
                    <h4 class="modal-title text-wrap">{{$title}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-wrap">
                    <p>{!! $description !!}</p>
                    <p>¿Deseas continuar?</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button"  onclick="event.preventDefault(); document.getElementById('buttonconfirm-form-{{$id}}').submit();"

                        @if($type == "REMOVE")
                            class="btn btn-danger"
                        @elseif($type == "INFO")
                            class="btn btn-info"
                        @endif

                            data-dismiss="modal">

                        @if($type == "REMOVE")
                            <i class="fas fa-trash"></i> &nbsp;Sí, eliminar
                        @elseif($type == "INFO")
                            Sí, aceptar
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="buttonconfirm-form-{{$id}}" action="{{ route($route)}}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="_id" value="{{$id}}"/>
    </form>
</div>
