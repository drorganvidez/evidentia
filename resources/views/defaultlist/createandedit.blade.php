@extends('layouts.app')

@isset($edit)
    @section('title', 'Editar lista: '.$defaultlist->name)
@else
    @section('title', 'Crear nueva lista')
@endisset

@section('title-icon', 'fas fa-stream')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.defaultlist.list',['instance' => $instance])}}">Gestionar listas</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="{{$route}}">
                        @csrf

                        <x-id :id="$defaultlist->id ?? ''" :edit="$edit ?? ''"/>

                        <div class="form-row">

                            <x-input col="6" attr="name" :value="$defaultlist->name ?? ''" label="Título" description="Escribe un título para tu lista."/>

                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-12">
                                <label>Seleccionar alumnos</label>
                                <small class="form-text text-muted">Selecciona las alumnas y alumnos que deseas que formen parte de la lista.</small>
                                <select id="users" name="users[]" class="duallistbox" multiple="multiple @error('users') is-invalid @enderror">
                                    @foreach($users as $user)
                                        <option

                                            @isset($defaultlist)
                                            @if($defaultlist->users->contains($user))
                                                selected
                                            @endif
                                            @endisset

                                            {{$user->id == old('user') ? 'selected' : ''}} value="{{$user->id}}">
                                            {{$user->surname}}, {{$user->name}}
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

                        <div class="row">
                            <div class="col-lg-3 mt-1">
                                <button type="submit"  class="btn btn-primary btn-block">Guardar lista</button>
                            </div>
                        </div>


                    </form>
                </div>

            </div>


        </div>
    </div>

@endsection
