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

                {{-- Mass Actions --}}
                <x-slot:mass_actions>
                    Exportar evidencias, evidences.export.mass, fe fe-download-cloud
                </x-slot:mass_actions>

                {{-- Edit evidence --}}
                <x-slot:edit_item_route>
                    evidences.edit
                </x-slot:edit_item_route>

                {{-- Delete evidence --}}
                <x-slot:delete_item_message>
                    Esto borrará la evidencia actual, las ediciones anteriores <b>y todos los archivos adjuntos</b>
                </x-slot:delete_item_message>
                <x-slot:delete_item_route>
                    evidences.delete_p
                </x-slot:delete_item_route>


            </x-data-table>

        </div>

    </div>


@endsection
