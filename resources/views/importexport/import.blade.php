@extends('layouts.app')

@section('title', 'Importaciones')
@section('title-icon', 'nav-icon fas fa-file-import')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-12">
            <x-status/>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#users" data-toggle="tab">Importar alumnos</a></li>
                        <li class="nav-item"><a class="nav-link" href="#instances" data-toggle="tab">Importar instancias</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        <div class="active tab-pane" id="users">

                            <div class="row">
                                <div class="col-lg-6">

                                    <div class="callout callout-info">
                                        <h5>¡Fácil y rápido!</h5>

                                        <p>Puedes importar un XLS con todos los alumnos y alumnas de este curso de una sola vez.
                                            Por defecto, todos los nuevos usuarios tendrán el rol de ESTUDIANTE</p>
                                    </div>

                                    <form method="POST" enctype="multipart/form-data" action="{{$route}}">

                                        @csrf

                                        <div class="col-lg-12">

                                            <div class="form-row">

                                                <x-input col="12" attr="xls" id="files" type="file" :required="false" label="Archivo XLS"/>

                                                <div class="col-lg-6 mt-1">
                                                    <button type="submit"  class="btn btn-primary btn-block">Importar alumnos</button>
                                                </div>
                                            </div>

                                        </div>

                                    </form>

                                </div>

                                <div class="col-lg-6">


                                    <div class="card card-primary card-outline">

                                        <div class="card-header">
                                            <h5 class="m-0">¡Un consejo!</h5>
                                        </div>

                                        <div class="card-body">

                                            <p>Con objeto de garantizar un buen funcionamiento de esta herramienta, se recomienda
                                                que el archivo XLS siga la siguiente disposición de columnas:</p>

                                            <div class="table-responsive">
                                                <table class="table table-hover m-0">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col">D.N.I.</th>
                                                        <th scope="col">Apellidos, Nombre</th>
                                                        <th scope="col">Uvus</th>
                                                        <th scope="col">Grupo</th>
                                                        <th scope="col">Correo</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr scope="row">
                                                        <td>77777777</td>
                                                        <td>POLO POLO, MARCO</td>
                                                        <td>marpolpol</td>
                                                        <td>Grupo 1</td>
                                                        <td>polo@mail.com</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <p>Como consejos de interés:</p>

                                            <ul>
                                                <li>No subir XLS con celdas combinadas</li>
                                                <li>Los campos <i>DNI, Uvus, email</i> son únicos; si existen dos usuarios con esa misma información, solo
                                                    se importará el primero de ellos.</li>
                                            </ul>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="instances">
                        </div>


                    </div>
                </div>
            </div>
        </div>



@endsection
