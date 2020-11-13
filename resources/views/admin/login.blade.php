@extends('home.main')

@section('title', 'Acceder')

@section('content')

    <div class="card">

        <div class="card-body login-card-body">

            <p class="login-box-msg">Acceso a la administración</p>

            <x-status/>

            <form method="POST" action="{{ route('admin.login_p') }}">
                @csrf

                <div class="input-group mb-3">
                    <input name="email" required type="email" class="form-control" placeholder="Email"  autocomplete="email" autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
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
    </div>
@endsection

