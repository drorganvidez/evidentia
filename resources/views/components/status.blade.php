@if (session('success'))

    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-check"></i> ¡Felicidades!</h5>
        {{ session('success') }}
    </div>

@endif

@if (session('error'))

    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
        {{ session('error') }}
    </div>

@endif
