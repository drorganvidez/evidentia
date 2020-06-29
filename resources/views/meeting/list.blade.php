@extends('layouts.app')

@section('title', 'Gestionar reuniones')

@section('title-icon', 'far fa-list-alt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-status/>

            <div class="row mb-3">
                <div class="col-lg-2 mt-1">
                    <a href="{{route('secretary.meeting.create',['instance' => $instance])}}" class="btn btn-primary btn-block" role="button"><i class="fas fa-plus"></i> &nbsp;Crear nueva reunión</a>
                </div>
            </div>

            <div class="card">


                <div class="card-body table-responsive p-0">

                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap m-0">
                            <thead>
                            <tr>
                                <th scope="col">Título de la reunión</th>
                                <th scope="col">Realizada</th>
                                <th scope="col">Herramientas</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($meetings as $meeting)
                                <tr scope="row">
                                    <td>{{$meeting->title}}</td>
                                    <td>{{ \Carbon\Carbon::parse($meeting->datetime)->diffForHumans() }}</td>
                                    <td>
                                        <a class="btn btn-primary btn-sm"
                                           href="{{route('secretary.defaultlist.edit',['instance' => $instance, 'id' => $defaultlist->id])}}"
                                           role="button">
                                            <i class="far fa-edit"></i>
                                            Editar lista</a>

                                        <x-buttonconfirm :id="$meeting->id" route="secretary.defaultlist.remove" title="¿Seguro?" description="Esta acción es permanente." type="REMOVE" />

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>

            </div>

            {{ $meetings->links() }}


        </div>
    </div>

@endsection
