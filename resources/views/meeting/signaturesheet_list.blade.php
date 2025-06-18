@extends('layouts.app')

@section('title', 'Mis hojas de firmas')

@section('title-icon', 'fas fa-list')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('secretary.meeting.manage') }}">Gestionar reuniones</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <x-menumeeting />

        <div class="col-lg-8">

            <div class="card">

                <div class="card-body">

                    <table id="dataset" class="table table-hover table-responsive">
                        <thead>
                            <tr>
                                <th scope="col">Título</th>
                                <th scope="col">Convocatoria</th>
                                <th scope="col">Última modificación</th>
                                <th scope="col">URL para firmar</th>
                                <th scope="col">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($signature_sheets as $signature_sheet)
                                <tr scope="row">
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                        <a
                                            href="{{ route('secretary.meeting.manage.signaturesheet.view', ['signature_sheet' => $signature_sheet]) }}">
                                            {{ $signature_sheet->title }}
                                        </a>
                                    </td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                        {{ $signature_sheet->meetingRequest->title ?? '' }}
                                    </td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                        {{ \Carbon\Carbon::parse($signature_sheet->updated_at)->diffForHumans() }}
                                    </td>
                                    <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">

                                        <span id="signature_sheets_{{ $signature_sheet->id }}">
                                            <a href="{{ URL::to('/') }}/sign/{{ $signature_sheet->random_identifier }}"
                                                target="_blank">
                                                {{ URL::to('/') }}/sign/{{ $signature_sheet->random_identifier }}
                                            </a>
                                        </span>

                                    </td>
                                    <td>
                                        <button onclick="copyToClipboard('#signature_sheets_{{ $signature_sheet->id }}')"
                                            type="button" class="btn btn-light btn-xs"><i class="far fa-copy"></i>
                                            Copiar</button>

                                        <a class="btn btn-primary btn-xs"
                                            href="{{ route('secretary.meeting.manage.signaturesheet.view', ['signature_sheet' => $signature_sheet]) }}">
                                            <i class="fas fa-signature"></i>
                                        </a>

                                        <a class="btn btn-info btn-xs"
                                            href="{{ route('secretary.meeting.manage.signaturesheet.edit', ['id' => $signature_sheet->id]) }}"><i
                                                class="fas fa-edit"></i></a>

                                        <a class="btn btn-danger btn-xs" href="#" data-toggle="modal"
                                            data-target="#modal-confirm-REMOVE-{{ $signature_sheet->id }}">
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

    @foreach ($signature_sheets as $signature_sheet)
        <div class="modal fade" id="modal-confirm-REMOVE-{{ $signature_sheet->id }}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="overflow: visible">
                    <div class="modal-header">
                        <h4 class="modal-title text-wrap">¿Seguro?</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body text-wrap">
                        <p>Las firmas <b>se borrarán permanentemente.</b></p>
                        <p>Si hay alguna convocatoria asociada, no se verá afectada, solo se desparejará.</p>
                        <p>Ningún acta se verá afectada.</p>
                        <p>¿Deseas continuar?</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

                        <form id="buttonconfirm-form-{{ $signature_sheet->id }}"
                            action="{{ route('secretary.meeting.manage.signaturesheet.remove') }}" method="post">
                            @csrf

                            <input type="hidden" name="signature_sheet_id" value="{{ $signature_sheet->id }}" />

                        </form>

                        <button type="buton"
                            onclick="event.preventDefault(); document.getElementById('buttonconfirm-form-{{ $signature_sheet->id }}').submit();"
                            class="btn btn-danger" data-dismiss="modal">
                            <i class="fas fa-trash"></i> &nbsp;Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


@endsection
