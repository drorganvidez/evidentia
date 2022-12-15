@extends('layouts.app')

@section('title', 'Gestionar alumnos')

@section('title-icon', 'nav-icon fas fa-users-cog')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')


    <div class="row">
        <div class="col-lg-8">

            <div class="row mb-3">
                <p style="padding: 5px 25px 0px 15px">Exportar tabla:</p>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('president.manage.student.export',['instance' => $instance, 'ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('president.manage.student.export',['instance' => $instance, 'ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('president.manage.student.export',['instance' => $instance, 'ext' => 'pdf'])}}"
                       class="btn btn-info btn-block" role="button">
                        PDF</a>
                </div>
            </div>
        </div>
            <div class="col-lg-12">

            <div class="card shadow-lg">

                <div class="card-body">

                    <div class="row" style="margin-bottom: 20px">
                        <div class="col-lg-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default">Acciones</button>
                                <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu" style="">

                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('LECTURE'))

                                        <a class="dropdown-item" href="{{route('lecture.import',\Instantiation::instance())}}">Importación masiva de usuarios</a>

                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#borrar_todo">
                                        Borrar todos los usuarios
                                    </a>

                                    @endif

                                    @if(\Illuminate\Support\Facades\Auth::user()->hasRole('PRESIDENT'))
                                            <a class="dropdown-item" href="#">Coming soon...</a>
                                    @endif
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>

                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">DNI</th>
                            <th>Apellidos</th>
                            <th>Nombre</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">UVUS</th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell">Roles</th>
                            <th></th>
                            <th class="d-none d-sm-none d-md-table-cell d-lg-table-cell" style="width: 15rem;">Almacenamiento ocupado</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($users as $user)

                            @if($user->id != Auth::id())

                                @if($user->block)
                                    <tr class="bg-danger">
                                @else
                                    <tr>
                                @endif

                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->dni}}</td>
                                        <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->surname}}</a></td>
                                        <td><a  href="{{route('profiles.view',['instance' => $instance, 'id' => $user->id])}}">{{$user->name}}</a></td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">{{$user->username}}</td>
                                        <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                            <x-roles :user="$user"/>
                                        </td>
                                        <td>
                                            @if(Auth::user()->hasRole('PRESIDENT'))
                                                <a class="btn btn-primary btn-sm" href="{{route('president.user.management',['instance' => $instance, 'id' => $user->id])}}">
                                                    <i class="nav-icon nav-icon fas fa-users-cog"></i>
                                                </a>
                                            @else
                                                <a class="btn btn-primary btn-sm" href="{{route('lecture.user.management',['instance' => $instance, 'id' => $user->id])}}">
                                                    <i class="nav-icon nav-icon fas fa-users-cog"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Espacio dedicado para el sumatorio de espacio ocupado --}}
                                            {{-- El link redirige al file manager del usuario en concreto--}}
                                            <a href="{{route('lecture.user.filemanager', ['instance' => $instance, 'id' => $user->id])}}">
                                                {{$dict_storage[$user->id]}}
                                            </a>
                                        </td>
                                    </tr>

                            @endif

                        @endforeach

                        </tbody>
                    </table>

                </div>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Añadir nuevo usuario</h4>

                    <form method="POST" enctype="multipart/form-data" action="{{route('management.user.new',\Instantiation::instance())}}">
                        @csrf

                        <div class="form-row">
                            <x-input col="6" attr="name" label="Nombre"/>
                            <x-input col="6" attr="surname" label="Apellidos"/>
                        </div>

                        <div class="form-row">
                            <x-input col="6" attr="email" label="Email"/>
                        </div>

                        <div class="form-row">
                            <x-input col="6" attr="username"  label="UVUS" description="El UVUS será el nombre de usuario"/>
                            <x-input col="6" attr="password" disabled="true" :edit="true" label="Password" description="La contraseña por defecto es aleatoria y debe ser restablecida"/>
                        </div>
                        <div class="form-row">

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary btn-block" data-dismiss="modal">
                                    <i class="fas fa-user-plus"></i>
                                    Añadir usuario</button>
                            </div>

                        </div>



                    </form>

                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="borrar_todo">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="overflow: visible">

                <div class="modal-header">
                    <h4 class="modal-title text-wrap">Borrar todos los usuarios</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>


                <form action="{{route('management.user.delete.all',\Instantiation::instance())}}" method="POST">
                    @csrf
                    <div class="modal-body text-wrap">
                        <h2>Aviso</h2>
                        <p>
                            Esta acción está pensada al principio del uso de este software, cuando se
                            acaban de importar todos los usuarios mediante un archivo XLS y/o se están haciendo pruebas. Esta función es <b>muy destructiva.</b>
                        </p>

                        <p>Se borrarán:</p>

                        <ul>
                            <li>Todos los usuarios excepto el tuyo</li>
                            <li>Todas las evidencias asociadas</li>
                            <li>Todos los mensajes y notificaciones asociadas</li>
                            <li>Todas las asistencias</li>
                            <li>Todas las reuniones registradas por los secretarios</li>
                        </ul>

                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> &nbsp;Lo comprendo, eliminar todos los usuarios
                        </button>
                    </div>
                </form>




            </div>
        </div>
    </div>

@endsection
