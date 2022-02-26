<div class="toast-container" style="position: absolute; top: 10px; right: 10px;">
    <div class="toast bg-danger text-white fade show" id="toast_error" style="display: none">
        <div class="toast-header bg-danger text-white">
            <strong class="me-auto"><i class="bi-gift-fill"></i> ¡Ops!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            {{session('error')}}
        </div>
    </div>
</div>

<div class="toast-container" style="position: absolute; top: 10px; right: 10px;">
    <div class="toast bg-success text-white fade show" id="toast_success" style="display: none">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto"><i class="bi-gift-fill"></i> ¡Genial!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            {{session('success')}}
        </div>
    </div>
</div>

<div class="toast-container" style="position: absolute; top: 10px; right: 10px;">
    <div class="toast bg-light text-white fade show" id="toast_light" style="display: none">
        <div class="toast-header bg-light text-black">
            <strong class="me-auto"><i class="bi-gift-fill"></i> Mensaje</strong>
            <button type="button" class="btn-close btn-close-black" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body text-black">
            {{session('light')}}
        </div>
    </div>
</div>

