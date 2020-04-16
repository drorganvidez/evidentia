<a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#modal-remove-{{$id}}">
    <i class="fas fa-trash"></i>
    Eliminar
</a>

<div class="container">
<div class="modal fade" id="modal-remove-{{$id}}">
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
                <button type="button"  onclick="event.preventDefault(); document.getElementById('buttonremove-form-{{$id}}').submit();" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-trash"></i> &nbsp;Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<form id="buttonremove-form-{{$id}}" action="{{ route($route,$instance) }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="_id" value="{{$id}}"/>
</form>
</div>
