@extends('layouts.app')

@section('title', 'Asignar roles')

@section('title-icon', 'nav-icon fas fa-cogs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <form method="POST" action="{{$route}}">
        @csrf

        <div class="row">


                <div class="col-lg-8">

                    <div class="card shadow-lg">

                        <div class="card-body">

                            <h3>Roles a aplicar</h3>

                            <div class="form-group">
                                <select class="select2bs4" id="roles" name="roles[]" multiple="multiple @error('roles') is-invalid @enderror" data-placeholder="Elige el rol o los roles a aplicar"
                                        style="width: 100%;">
                                    @foreach($roles as $rol)
                                        <option
                                            @if($rol->id == "6")
                                            selected
                                            @endif
                                            value="{{$rol->id}}"
                                        >{{$rol->slug}}</option>
                                    @endforeach
                                </select>
                                @error('roles')
                                <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                @enderror
                            </div>

                            <div class="form-group" id="comittee" style="display: none">
                                <label>Selecciona el comité asociado</label>
                                <select name="comittee" class="form-control select2bs4" style="width: 100%;">
                                    @foreach($comittees as $comittee)
                                        <option
                                            value="{{$comittee->id}}"
                                        >{{$comittee->name}}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Los roles "COORDINADOR", "SECRETARIO" o "COLABORADOR"
                                    exigen tener un comité asociado.</small>
                            </div>

                            <h3>Usuarios</h3>
                            
                            <label style="color:red;">Aviso: Los usuarios seleccionados perderán sus roles antes de conseguir sus roles nuevos.</label>

                            <div class="form-group">
                                <select class="select2bs4" id="users" name="users[]" multiple="multiple @error('users') is-invalid @enderror" data-placeholder="Elige los usuarios a los que aplicar los roles"
                                        style="width: 100%;">
                                    @foreach($users as $user)
                                        <option
                                            value="{{$user->id}}"
                                        >{{$user->name}} {{$user->surname}}</option>
                                    @endforeach
                                </select>
                                @error('users')
                                <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                @enderror
                            </div>

                            <div class="form-row">
                                <div class="col-lg-3 mt-1">
                                    <button type="submit"  class="btn btn-primary btn-block">Actualizar usuarios</button>
                                </div>
                            </div>

                    </div>

                </div>


        </div>

    </form>

    @section('scripts')

        <script>

            var values = $("#roles").val();
            for (var i = 0; i < values.length; i++) {

                // si es coordinador
                if(values[i] == "4"){
                    $("#comittee").show();
                }

                // si es administrador
                if(values[i] == "5"){
                    $("#comittee").show();
                }

                // si es colaborador
                if(values[i] == "7"){
                    $("#comittee").show();
                }
            }

            $("#roles").change(function(){
                var values = $(this).val();
                var cont = 0;
                for (var i = 0; i < values.length; i++) {

                    // si es coordinador
                    if(values[i] == "4"){
                        $("#comittee").show();
                        cont++;
                    }

                    // si es administrador
                    if(values[i] == "5"){
                        $("#comittee").show();
                        cont++;
                    }

                    // si es colaborador
                    if(values[i] == "7"){
                        $("#comittee").show();
                        cont++;
                    }
                }

                if(cont == 0){
                    $("#comittee").hide();
                }

            });

        </script>

    @endsection

@endsection
