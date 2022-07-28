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
                    Comité | committee | {"type" : "badge"};
                    Última modificación | updated_at | {"type" : "ago"}
                </x-slot:columns>


            </x-data-table>

        </div>

    </div>


@endsection
