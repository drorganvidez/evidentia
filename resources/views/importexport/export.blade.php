@extends('layouts.app')

@section('title', 'Exportaciones')
@section('title-icon', 'nav-icon fas fa-file-export')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">

                        <div class="active tab-pane" id="evidences">

                            <div class="row">
                                <div class="col-lg-6">

                                    <div class="callout callout-info">
                                        <h5>¡Fácil y rápido!</h5>

                                        <p>Puedes exportar un XLS con las evidencias, reuniones y asistencias de cada
                                        alumno y alumna.</p>
                                    </div>

                                    <form method="POST" action="{{$route}}">

                                        @csrf

                                        <div class="col-lg-12">

                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group">Elige qué quieres incluir en el Excel:</div>
                                                    <div class="form-group">

                                                        <div class="form-check">
                                                            <input class="form-check-input" name="evidences" type="checkbox" checked="">
                                                            <label class="form-check-label">Evidencias</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" name="meetings" type="checkbox"  checked="">
                                                            <label class="form-check-label">Reuniones</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" name="events" type="checkbox"  checked="">
                                                            <label class="form-check-label">Eventos</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" name="bonus" type="checkbox"  checked="">
                                                            <label class="form-check-label">Bono de horas</label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-row">

                                                <div class="col-lg-6 mt-1">
                                                    <button type="submit"  class="btn btn-primary btn-block">Exportar evidencias</button>
                                                </div>
                                            </div>

                                        </div>

                                    </form>

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
