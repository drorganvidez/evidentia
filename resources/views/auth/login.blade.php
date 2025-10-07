@extends('home.main')

@section('title', 'Acceder')

@section('content')

    <div class="card lg mx-auto" style="max-width: 380px;">
        <div class="card-body login-card-body">

            <form action="{{ route('login') }}" method="post" novalidate>
                @csrf

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Correo electrónico"
                        autocomplete="email" autofocus required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña"
                        autocomplete="current-password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <a href="{{ route('password.custom_reset') }}" class="text-sm mt-2 mb-3">
                        He olvidado mi contraseña o soy nuev@ en Evidentia
                    </a>
                </div>

                <div class="input-group">
                    <a href="https://t.me/evidentia_sat" class="text-sm mb-3">
                        Necesito asistencia técnica (grupo de Telegram)
                    </a>
                </div>

                <div class="row">
                    <div class="col-8">
                    </div>
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
