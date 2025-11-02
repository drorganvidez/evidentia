@extends('home.main')

@section('title', 'Acceder')

@section('content')

    <div class="card lg mx-auto" style="max-width: 380px;">
        <div class="card-body login-card-body">

            {{-- ALERTA DE ERROR EN ROJO --}}
            @if ($errors->any())
                <div class="alert alert-danger text-center fw-bold d-flex align-items-center justify-content-center"
                    role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    CREDENCIALES INVÁLIDAS
                </div>
                <div class="text-center mb-3">
                    <a href="{{ route('password.custom_reset') }}" class="text-danger text-decoration-underline">
                        ¿Has olvidado tu contraseña?
                    </a>
                </div>
            @endif

            <form action="{{ route('login') }}" method="post" novalidate>
                @csrf

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Correo electrónico"
                        autocomplete="email" value="{{ old('email') }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña"
                        autocomplete="current-password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-2">
                    <a href="{{ route('password.custom_reset') }}" class="text-sm">
                        He olvidado mi contraseña o soy nuev@ en Evidentia
                    </a>
                </div>

                <div class="input-group mb-3">
                    <a href="https://t.me/evidentia_sat" class="text-sm">
                        Necesito asistencia técnica (grupo de Telegram)
                    </a>
                </div>

                <div class="row">
                    <div class="col-8"></div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">
                            Acceder
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@endsection
