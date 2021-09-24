@extends('layouts.app')

@section('title', 'Gestionar comités')

@section('title-icon', 'fas fa-sitemap')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">

                    <form method="POST" action="{{$route}}">
                @csrf

                <table id="dataset" class="table table-hover table-responsive">
                    <thead>
                    <tr>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Previsualización</th>
                        <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Icono de Font Awesome</th>
                        <th>Nombre del comité</th>
                        <th>Opciones</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($comittees as $comittee)
                        <tr>
                            <td class="align-middle text-center d-none d-sm-none d-md-table-cell d-lg-table-cell"><span id="icon_prev_{{$comittee->id}}" style="font-size: 20px">{!! $comittee->icon ?? '' !!}</span></td>
                            <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell"><input name="icon_{{$comittee->id}}" id="icon_{{$comittee->id}}" oninput="prev({{$comittee->id}})" type="text" class="form-control" placeholder="" value="{{$comittee->icon}}" autocomplete="icon" autofocus=""></td>
                            <td><input name="name_{{$comittee->id}}" type="text" class="form-control" placeholder="" value="{{$comittee->name}}" autocomplete="name" ></td>
                            <td>
                                <a class="form-control btn btn-danger " href="#" data-toggle="modal" data-target="#modal-{{$comittee->id}}">
                                    <i class="fas fa-trash"></i> <span class="d-none d-sm-none d-md-none d-lg-inline">Eliminar</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>

                <div class="form-row">
                    <div class="col-lg-3 mt-1">
                        <button type="submit"  class="btn btn-primary btn-block">Guardar comités</button>
                    </div>
                </div>



            </form>

                </div>

            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Crear nuevo comité</h4>

                    <p>Puedes encontrar más iconos en <a target="_blank" href="https://fontawesome.com/icons?d=gallery">Font Awesome</a></p>

                    <form method="POST" action="{{$route_new}}">
                        @csrf

                        <div class="form-row">

                            <div class="form-group col-lg-2">
                                <label for="icon">Prev.</label>
                                <span id="write_new_icon" class="align-middle form-control input-group-text text-center"></span>
                            </div>

                            <x-input col="10" id="new_icon" attr="icon" :required="false" label="Icono de Font Awesome" />

                            <x-input col="12" attr="name"  label="Nombre del comité" />

                        </div>

                        <div class="form-row">
                            <div class="form-group col-12 col-lg-6 col-sm-4">
                                <button type="submit" class="btn btn-primary btn-block">Crear comité</button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>
        </div>



    </div>

    <div class="container">
        @foreach($comittees as $comittee)
        <div class="modal fade" id="modal-{{$comittee->id}}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="overflow: visible">

                    <div class="modal-header">
                        <h4 class="modal-title text-wrap">Eliminar comité</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    @if($comittee->can_be_removed())

                        <form action="{{$route_remove}}" method="POST">
                            @csrf
                            <input type="hidden" name="_id" value="{{$comittee->id}}"/>
                            <div class="modal-body text-wrap">
                                Esta acción no se puede deshacer. ¿Deseas continuar?
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> &nbsp;Eliminar comité
                                </button>
                            </div>
                        </form>

                    @else

                        <div class="modal-body text-wrap">
                            <p>No es posible eliminar este comité. Un comité puede ser eliminado si se cumplen las <b>tres condiciones siguientes</b>:
                                <ol>
                                    <li>
                                        <b>No tiene ningún coordinador ni secretario</b><br>
                                        (coordinadores: {{$comittee->coordinators->count()}}, secretarios: {{$comittee->secretaries->count()}})
                                    </li>
                                    <li><b>No tiene evidencias asociadas, sea cual sea su estado</b> (evidencias: {{$comittee->evidences->count()}})</li>
                                <li><b>No hay reuniones asociadas</b> (reuniones: {{$comittee->meetings->count()}})</li>
                                </ol>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>

                    @endif



                </div>
            </div>
        </div>
        @endforeach
    </div>

    @section('scripts')

    <script>

        // previsualizar icono a la hora de crear un nuevo comité
        $("#new_icon").on('input',function(){
            var val = $("#new_icon").val();
            $("#write_new_icon").html(val);
        });

        // previsualizar icono a la hora de modificar un comité
        function prev(id){
            var val = $("#icon_"+id).val();
            $("#icon_prev_"+id).html(val);
        }

    </script>

    @endsection

@endsection
