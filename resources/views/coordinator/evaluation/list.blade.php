@extends('layouts.app')

@section('title', 'Evaluación')

@section('submenu')

    <x-submenus.evaluation-menu/>

@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">

            <x-data-table>

                {{-- Data --}}
                <x-slot:data>
                    {{$evidences}}
                </x-slot:data>

                {{-- Columns --}}
                <x-slot:columns>
                    Título | title | {"type" : "href", "route" : "evidences.view"};
                    Horas | hours;
                    Estudiante | owner;
                    Estado | spanish_status | {"type" : "badge"};
                    Última modificación | updated_at | {"type" : "ago"}
                </x-slot:columns>

                {{-- Filters --}}
                <x-slot:filters>
                    Estado, status, Pendiente:Aceptada:Rechazada
                </x-slot:filters>

                {{-- Actions --}}
                <x-slot:actions>
                    Moderar, coordinator.evidences.moderate.evidence, fe fe-check;
                    Exportar, evidences.export, fe fe-download-cloud
                </x-slot:actions>

                {{-- Mass Actions --}}
                <x-slot:mass_actions>
                    Exportar evidencias, evidences.export.mass, fe fe-download-cloud
                </x-slot:mass_actions>


            </x-data-table>

        </div>

    </div>


@endsection
