@extends('auth.root')

@section('title', 'Administración')

@section('content')

    <!-- Form -->
    <form action="{{route('admin.login_p')}}" method="post">
    @csrf

    <!-- Email address -->
        <div class="form-group">

            <!-- Label -->
            <label class="form-label">
                Correo
            </label>

            <!-- Input -->
            <input name="email" class="form-control"
                   placeholder="Introduce tu correo" autofocus>

        </div>

        <!-- Password -->
        <div class="form-group">
            <div class="row">
                <div class="col">

                    <!-- Label -->
                    <label class="form-label">
                        Contraseña
                    </label>

                </div>
                <div class="col-auto">

                    <!-- Help text -->
                    <a href="{{route('password.reset',\Instantiation::instance())}}" class="form-text small text-muted">
                        ¿Has olvidado la contraseña?
                    </a>

                </div>
            </div> <!-- / .row -->

            <!-- Input group -->
            <div class="input-group input-group-merge">

                <!-- Input -->
                <input class="form-control" id="password" name="password" type="password" placeholder="Introduce tu contraseña">

                <!-- Icon -->
                <x-show-password/>

            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-lg w-100 btn-primary mb-3">
            Acceder
        </button>

    </form>


@endsection

