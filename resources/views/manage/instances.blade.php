@extends('layouts.app')

@section('title', 'Instancias')

@section('title-icon', 'fas fa-boxes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">



                    <form method="POST" action="{{$route}}">
                        @csrf

                        <div class="callout">
                            <div class="form-row">
                                <div class="form-group col-lg-12">
                                    <h5>Crear nuevo curso</h5>
                                    Para más opciones de configuración de cada instancia, recomendamos visitar <a href="{{route('admin.instance.manage')}}">la administración</a>
                                </div>

                                    <x-input col="6" attr="name" label="Nombre del curso" description="Ejemplo: curso 2019/2020"/>

                            </div>
                            <div class="form-row">
                                <div class="form-group col-12 col-lg-4 col-sm-4">
                                    <button type="submit" class="btn btn-primary btn-block">Crear curso</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <table id="dataset" class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">Curso</th>
                            <th scope="col">Ruta</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($instances as $instance)
                            <tr>
                                <td>{{$instance->name}}</td>
                                <td>{{$instance->route}}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>

@endsection
