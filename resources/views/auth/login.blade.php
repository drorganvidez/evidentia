@extends('auth.root')

@section('title', 'Bienvenid@ a Evidentia')

@section('content')

    <!-- Form -->
    <form action="{{route('instance.login_p',\Instantiation::instance())}}" method="post">
    @csrf

        <div class="row">
            <x-input>
                <x-slot:name>
                    username
                </x-slot:name>
                <x-slot:placeholder>
                    Introduce tu usuario
                </x-slot:placeholder>
                <x-slot:label>
                    UVUS
                </x-slot:label>
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
                <input class="form-control" id="password" name="password" type="password" placeholder="Introduce tu contraseña" required autofocus>

                <!-- Icon -->
                <x-show-password/>

            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-lg w-100 btn-primary mb-3">
            Acceder
        </button>

    </form>

    {{--

    <div class="card">

        <div class="card-body">

            ddddd

            <form action="{{route('instance.login_p',\Instantiation::instance())}}" method="post">
                @csrf

                <div class="row">

                    <div class="col-lg-6">
                        <div class="input-group mb-3">
                            <select id="subject" onchange="location = '/' + this.value;" class="selectpicker form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject') }}" required>

                                @foreach($instances as $i)

                                    <option @if($i->route ==  \Instantiation::instance()) selected @endif value="{{$i->route}}">{{$i->name}}</option>

                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6 text-md-right">

                    </div>
                </div>

                <div class="input-group mb-3">
                    <input name="username" required type="text" class="form-control" placeholder="UVUS"  autocomplete="username" autofocus>
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

                <div class="input-group">
                <a href="{{route('password.reset',\Instantiation::instance())}}" class="text-sm mt-2 mb-3">
                    He olvidado mi contraseña o soy nuev@ en Evidentia
                </a>
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

    --}}

@endsection
