@extends('layouts.app')

@section('title', 'Reasignando: ' . $evidence->title)

@section('content')

    <div class="row">

        <div class="col-lg-6">
            <a href="{{url()->previous() }}" class="btn btn-outline-primary mb-4">
                <i class="fe fe-skip-back"></i> Volver
            </a>
        </div>

    </div>


    <div class="row">

        <div class="col-lg-12">

            <div class="row">

                <div class="col-6">
                    <div class="card bg-light border">
                        <div class="card-body">

                            <!-- Title -->
                            <p class="mb-2">
                                Reasignar comité
                            </p>

                            <!-- Text -->
                            <p class="small text-muted mb-2">
                                Si te has equivocado asignando tu evidencia a un comité, puedes reasignarla aun estando pendiente de revisar.
                            </p>

                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="col-6">

            <form method="post" action="{{route('evidences.reassign_p', \Instantiation::instance())}}">

                <input type="hidden" name="_id" value="{{$evidence->id}}">

                @csrf

                <div class="row">

                    <div class="col-12">
                        <x-select>
                            <x-slot:data>
                                {{$committees}}
                            </x-slot:data>
                            <x-slot:col>
                                col-12
                            </x-slot:col>
                            <x-slot:description>
                                Comité actual: {{$evidence->committee->name}}
                            </x-slot:description>
                            <x-slot:option_name>
                                name
                            </x-slot:option_name>
                            <x-slot:name>
                                committee_id
                            </x-slot:name>
                            <x-slot:value>

                            </x-slot:value>
                        </x-select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-repeat"></i> &nbsp;Reasignar
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>


@endsection
