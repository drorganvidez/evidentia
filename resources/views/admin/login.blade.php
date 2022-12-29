@extends('auth.root')

@section('title', 'Administración')

@section('content')

    <!-- Form -->
    <form action="{{route('admin.login_p')}}" method="post">
    @csrf

        <div class="row">
            <x-input>
                <x-slot:name>
                    email
                </x-slot:name>
                <x-slot:placeholder>
                    Introduce tu correo
                </x-slot:placeholder>
                <x-slot:label>
                    Correo
                </x-slot:label>
                <x-slot:type>
                    email
                </x-slot:type>
                <x-slot:col>
                    col-12
                </x-slot:col>
                <x-slot:required></x-slot:required>

                @if(!old('username'))
                    <x-slot:autofocus>
                    </x-slot:autofocus>
                @endif

            </x-input>
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
                <a href="{{route('password.reset',\Instantiation::instance())}}" tabindex="-1" class="form-text small text-muted">
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

