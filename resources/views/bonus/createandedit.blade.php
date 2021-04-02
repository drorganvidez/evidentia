@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar bono: '.$bonus->reason)
@else
    @section('title', 'Crear bono')
@endisset

@section('title-icon', 'fas fa-puzzle-piece')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    @isset($edit)
        <li class="breadcrumb-item"><a href="{{route('secretary.bonus.list',['instance' => $instance])}}">Gestionar bonos</a></li>
    @endisset
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('info')
    <x-slimreminder :datetime="\Config::bonus_timestamp()"/>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <form method="POST" action="{{$route}}">
                        @csrf

                        <x-id :id="$bonus->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="reason" :value="$bonus->reason ?? ''" label="Razón" description="Escribe una razón para este bono de horas."/>

                            <x-input col="6" attr="hours" :value="$bonus->hours ?? ''" type="number" step="0.01" label="Horas" description="Números enteros o decimales."/>

                        </div>


                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label for="defaultlist">Elige una lista predeterminada</label>
                                <select id="defaultlist" onchange="getLista(this);" class="selectpicker form-control @error('defaultlist') is-invalid @enderror" value="{{ old('type') }}" required autofocus>

                                    <option value="-1">Selecciona una lista</option>
                                    @foreach($defaultlists as $defaultlist)
                                        <option value="{{trim($defaultlist->id)}}">{{trim($defaultlist->name)}}</option>
                                    @endforeach

                                </select>

                                @error('defaultlist')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12">
                                <label>Seleccionar alumnos</label>
                                <select id="users" name="users[]" class="duallistbox" multiple="multiple @error('users') is-invalid @enderror">
                                    @foreach($users as $user)
                                        <option

                                            @isset($bonus)
                                            @if($bonus->users->contains($user))
                                            selected
                                            @endif
                                            @endisset

                                            {{$user->id == old('user') ? 'selected' : ''}} value="{{$user->id}}">
                                            {{trim($user->surname)}}, {{trim($user->name)}}
                                        </option>
                                    @endforeach
                                </select>
                                @error('users')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                        </div>

                        <div class="form-row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit"  class="btn btn-primary btn-block">Guardar bono</button>
                            </div>
                        </div>


                    </form>
                </div>

            </div>


        </div>
    </div>

    @section('scripts')

        <script>

            function getLista(sel){

                if(sel.value != -1) {

                    var demo = $('.duallistbox').bootstrapDualListbox();
                    $("#users").empty();

                    var seleccionados = new Array();

                    $.ajax({
                        url: '/{{$instance}}/secretary/meeting/defaultlist/' + sel.value,
                        success: function (respuesta) {

                            for (var i = 0; i < respuesta.length; i++) {
                                demo.append('<option value="'+respuesta[i].id+'" selected>'+respuesta[i].surname+', '+respuesta[i].name+'</option>')
                                seleccionados.push(respuesta[i].id);
                            }

                            // Volvemos a incluir todos los usuarios
                            $.ajax({
                                url: '/{{$instance}}/gp/users/all/',
                                success: function (respuesta) {

                                    for (var i = 0; i < respuesta.length; i++) {
                                        if(seleccionados.indexOf(respuesta[i].id) == -1){
                                            demo.append('<option value="'+respuesta[i].id+'">'+respuesta[i].surname+', '+respuesta[i].name+'</option>')
                                        }

                                    }

                                    demo.bootstrapDualListbox('refresh');

                                },
                                error: function () {
                                    console.log("No se ha podido obtener la información de todos los usuarios");
                                }
                            });

                        },
                        error: function () {
                            console.log("No se ha podido obtener la información de la lista predeterminada");
                        }
                    });

                }
            }

        </script>

    @endsection

@endsection
