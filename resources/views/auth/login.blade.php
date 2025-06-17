@extends('home.main')

@section('title', 'Acceder')

@section('content')

    <div class="card shadow-lg">

        <div class="card-body">

            <form action="{{ route('login') }}" method="post">
                @csrf

                <div class="input-group mb-3">
                    <input name="email" required type="text" class="form-control" placeholder="UVUS"  autocomplete="email" autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb">
                    <input name="password" required type="password" class="form-control" placeholder="Contraseña" autocomplete="password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">
                                Recuérdame
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-6">
                        <button type="submit" class="btn btn-primary btn-block">Acceder</button>
                    </div>
                </div>
            </form>

        </div>
        <!-- /.login-card-body -->
    </div>

@endsection