@extends('layouts.app')

@section('title', 'Hoja de firma: '.$signature_sheet->title)

@section('title-icon', 'fas fa-list')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage',\Instantiation::instance())}}">Gestionar reuniones</a></li>
    <li class="breadcrumb-item"><a href="{{route('secretary.meeting.manage.signaturesheet.list',\Instantiation::instance())}}">Mis firmas</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting/>

        <div class="col-md-9">

            <div class="card shadow-sm">

                <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>
                                    <i class="fas fa-child"></i> Convocatoria asociada
                                </h4>
                                @if($signature_sheet->meeting_request)
                                <p>
                                    {{$signature_sheet->meeting_request->title}}
                                    <br>
                                    {{ \Carbon\Carbon::parse($signature_sheet->meeting_request->datetime)->format('d/m/Y') }}
                                    <br>
                                    {{ \Carbon\Carbon::parse($signature_sheet->meeting_request->datetime)->format('H:i') }}
                                    <br>
                                    {{$signature_sheet->meeting_request->place}}
                                </p>
                                @else
                                    No hay ninguna convocatoria asociada a esta hoja de firmas.
                                @endif
                            </div>
                            <div>

                                <div class="callout callout-info">
                                    <b>URL para firmar</b>
                                    <br>
                                    <span id="signature_sheets_{{$signature_sheet->id}}">
                                    <a href="{{URL::to('/')}}/{{$instance}}/sign/{{$signature_sheet->random_identifier}}" target="_blank">
                                        {{URL::to('/')}}/{{$instance}}/sign/{{$signature_sheet->random_identifier}}
                                    </a>
                                </span>
                                    <button onclick="copyToClipboard('#signature_sheets_{{$signature_sheet->id}}')"
                                            type="button" class="btn btn-light btn-xs"><i class="far fa-copy"></i> Copiar URL</button>
                                </div>


                            </div>
                        </div>

                        <hr>

                        <h4>
                            <i class="fas fa-signature"></i> Firmas ({{$signature_sheet->users->count()}})
                        </h4>

                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                        <tr>
                            <th scope="col">UVUS</th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Fecha y hora de firma</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($signature_sheet->users as $user)
                            <tr scope="row">
                                <td>
                                    {{$user->username}}
                                </td>
                                <td>
                                    {{$user->surname}}
                                </td>
                                <td>
                                    {{$user->name}}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($user->pivot->created_at)->format('d/m/Y') }}
                                    {{ \Carbon\Carbon::parse($user->pivot->created_at)->format('H:i:s') }}
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
