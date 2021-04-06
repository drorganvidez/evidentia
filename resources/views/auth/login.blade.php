@extends('home.main')

@section('title', 'Acceder')

@section('content')

    <div class="card shadow-lg">

        <div class="card-body">

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
                    He olvidado mi contraseña
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

@endsection
