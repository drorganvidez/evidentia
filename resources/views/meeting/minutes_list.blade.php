@extends('layouts.app')

@section('title', 'Mis actas')

@section('title-icon', 'fas fa-list')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage',\Instantiation::instance())}}">Gestionar reuniones</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting/>

        <div class="col-lg-9">

            <div class="row mb-3">
                <p style="padding: 5px 50px 0px 15px">Exportar tabla:</p>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('secretary.meeting.manage.minutes.export',['ext' => 'xlsx'])}}"
                       class="btn btn-info btn-block" role="button">
                        XLSX</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('secretary.meeting.manage.minutes.export',['ext' => 'csv'])}}"
                       class="btn btn-info btn-block" role="button">
                        CSV</a>
                </div>
                <div class="col-lg-1 mt-12">
                    <a href="{{route('secretary.meeting.manage.minutes.export',['ext' => 'pdf'])}}"
                       class="btn btn-info btn-block" role="button">
                        PDF</a>
                </div>
            </div>

            <div class="card shadow-sm">

                <div class="card-body">

                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th scope="col">Título</th>
                            <th scope="col">Lugar</th>
                            <th scope="col">Realizada</th>
                            <th scope="col">Duración</th>
                            <th scope="col">Última modificación</th>
                            <th scope="col">Opciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($meeting_minutes as $m)
                            <tr scope="row">
                                <td>
                                    {{$m->meeting->title}}
                                </td>
                                <td>
                                    {{$m->meeting->place}}
                                </td>
                                <td>
                                    {{\Carbon\Carbon::parse($m->meeting->datetime)->format('d/m/Y')}}
                                    {{\Carbon\Carbon::parse($m->meeting->datetime)->format('H:i')}}
                                </td>
                                <td>
                                    {{$m->meeting->hours}} horas
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($m->updated_at)->diffForHumans() }}
                                </td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="{{route('secretary.meeting.manage.minutes.download',['id' => $m->id])}}"><i class="fas fa-file-pdf"></i></a>

                                    <a class="btn btn-info btn-sm" href="{{route('secretary.meeting.manage.minutes.edit',['id' => $m->id])}}"><i class="fas fa-edit"></i></a>

                                    <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#modal-confirm-REMOVE-{{$m->id}}">
                                        <i class="fas fa-trash"></i>
                                    </a>

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>


        </div>
    </div>

    @foreach($meeting_minutes as $m)
        <div class="modal fade" id="modal-confirm-REMOVE-{{$m->id}}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="overflow: visible">
                    <div class="modal-header">
                        <h4 class="modal-title text-wrap">¿Seguro?</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body text-wrap">
                        <p>Esto borrará el acta, el archivo PDF, la reunión (con sus asistencias), los puntos y los acuerdos.</p>
                        <p>En caso de haber adjuntado alguna convocatoria y/o hoja de firmas, no serán modificadas.</p>
                        <p>¿Deseas continuar?</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                        <form id="buttonconfirm-form-{{$m->id}}" action="{{route('secretary.meeting.manage.minutes.remove',$instance)}}" method="post">
                            @csrf

                            <input type="hidden" name="meeting_minutes_id" value="{{$m->id}}"/>

                        </form>

                        <button type="buton" onclick="event.preventDefault(); document.getElementById('buttonconfirm-form-{{$m->id}}').submit();" class="btn btn-danger" data-dismiss="modal">
                            <i class="fas fa-trash"></i> &nbsp;Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
