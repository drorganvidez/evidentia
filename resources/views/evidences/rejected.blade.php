@extends('layouts.app')

@section('title', 'Evidencias')

@section('submenu')

    <x-submenus.evidences-menu/>

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
                    Comité | committee | {"type" : "badge"};
                    Puntuación | score | {"type" : "badge", "bg" : "danger"};
                    Última modificación | updated_at | {"type" : "ago"}
                </x-slot:columns>

                {{-- Filters --}}
                <x-slot:filters>
                    Comité, committee, @foreach($committees as $committee) {{$committee->name}} @if(!$loop->last): @endif @endforeach
                </x-slot:filters>

                {{-- Actions --}}
                <x-slot:actions>
                    Exportar, evidences.export, fe fe-download-cloud
                </x-slot:actions>

                {{-- Confirm Actions --}}
                <x-slot:confirm_actions>
                    Reeditar | evidences.reedit | fe fe-edit-3 | Puedes reeditar una evidencia rechazada. Esto hará que vuelva al estado de borrador. Tendrás que mandarla de nuevo a revisión.
                </x-slot:confirm_actions>

                {{-- Mass Actions --}}
                <x-slot:mass_actions>
                    Exportar evidencias, evidences.export.mass, fe fe-download-cloud
                </x-slot:mass_actions>


            </x-data-table>

        </div>

    </div>


@endsection