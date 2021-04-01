@extends('home.main')

@section('title', 'Actualizar contraseña')

@section('content')

    <div class="card shadow-sm">

        <div class="card-body login-card-body">
            <form action="{{route('password.update_p',["instance" => $instance, "token" => $token])}}" method="post">
                @csrf

                <div class="input-group mb-3">
                    <input name="password" required type="password" class="form-control" placeholder="Nueva contraseña" autocomplete="password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input name="password_confirmation" required type="password" class="form-control" placeholder="Repita nueva contraseña" autocomplete="password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-lg-12">
                        <button type="submit"  class="btn btn-primary btn-block">Actualizar contraseña</button>
                    </div>
                </div>

            </form>
        </div>


    </div>

@endsection

