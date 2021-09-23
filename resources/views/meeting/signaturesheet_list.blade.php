@extends('layouts.app')

@section('title', 'Mis hojas de firmas')

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
                            <th scope="col">URL para firmar</th>
                            <th scope="col">Opciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($signature_sheets as $signature_sheet)
                            <tr scope="row">
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{$signature_sheet->title}}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">
                                    {{ \Carbon\Carbon::parse($signature_sheet->created_at)->diffForHumans() }}
                                </td>
                                <td class="d-none d-sm-none d-md-table-cell d-lg-table-cell">

                                    <span id="signature_sheets_{{$signature_sheet->id}}">
                                        <a href="{{URL::to('/')}}/{{$instance}}/sign/{{$signature_sheet->random_identifier}}" target="_blank">
                                            {{URL::to('/')}}/{{$instance}}/sign/{{$signature_sheet->random_identifier}}
                                        </a>
                                    </span>

                                </td>
                                <td>
                                    <button onclick="copyToClipboard('#signature_sheets_{{$signature_sheet->id}}')"
                                            type="button" class="btn btn-light btn-xs"><i class="far fa-copy"></i> Copiar URL</button>
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
