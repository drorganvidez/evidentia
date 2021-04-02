@extends('home.main')

@section('title', 'Restablecer contraseña')

@section('content')

    <div class="card shadow-sm">

        <div class="card-body login-card-body">

            <form action="{{route('password.reset_p',$instance)}}" method="post">
                @csrf

                <div class="input-group mb-3">
                    <input name="email" required type="email" class="form-control" placeholder="Email"  autocomplete="username" autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <div class="row">

                <div class="col-sm-12 col-lg-12">
                    <button type="submit" class="btn btn-primary btn-block">Restablecer contraseña</button>

                </div>

                </div>

            </form>

        </div>

    </div>

@endsection
