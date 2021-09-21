@extends('layouts.app')

@section('title', 'Mis convocatorias')

@section('title-icon', 'fas fa-list')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage',\Instantiation::instance())}}">Gestionar reuniones</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting/>

        <div class="col-md-9">

            <div class="card shadow-sm">

                <div class="card-body">

                    <table id="dataset" class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">Título</th>
                            <th scope="col">Creada</th>
                            <th scope="col">Programada para</th>
                            <th scope="col">PDF</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($meeting_requests as $meeting_request)
                            <tr scope="row">
                                <td>
                                    {{$meeting_request->title}}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($meeting_request->created_at)->diffForHumans() }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($meeting_request->datetime)->format('d/m/Y') }}
                                    a las
                                    {{ \Carbon\Carbon::parse($meeting_request->datetime)->format('H:i') }}
                                    ({{ \Carbon\Carbon::parse($meeting_request->datetime)->diffForHumans() }})
                                </td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="{{route('secretary.meeting.manage.request.download',['instance' => $instance, 'id' => $meeting_request->id])}}"><i class="fas fa-file-pdf"></i> Descargar</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>


        </div>
    </div>


@endsection
