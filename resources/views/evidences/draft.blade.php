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
                    Título | title;
                    Horas | hours;
                    Comité | committee | {"type" : "badge"};
                    Última modificación | updated_at | {"type" : "ago"}
                </x-slot:columns>

                {{-- Filters --}}
                <x-slot:filters>
                    Comité, committee, @foreach($committees as $committee) {{$committee->name}} @if(!$loop->last): @endif @endforeach
                </x-slot:filters>

                {{-- Edit evidence --}}
                <x-slot:edit_item_route>
                    evidences.edit
                </x-slot:edit_item_route>


            </x-data-table>

        </div>

    </div>


@endsection
