@extends('layouts.app')

@section('title', 'Ver tarea: '.$task->title)

@section('title-icon', 'fab fa-battle-net')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/{{$instance}}">Home</a></li>
    
    <li class="breadcrumb-item"><a href="{{route('task.list',$instance)}}">Mis tareas</a></li>


    <li class="breadcrumb-item active">@yield('title')</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-lg">

                <div class="card-body">

                    <h5><b>Comité:</b><x-taskcomittee :task="$task"/></h5>
                    <h5><b>Horas:</b> {{$task->hours}} horas</h5>
                    <h5><b>Fecha Inicio:</b> 12:00</h5>
                    <h5><b>Fecha Inicio:</b> 15:00</h5>
                    <h5><b>Descripción:</b></h5>  {!! $task->description !!}

<!--
                        <a class="btn btn-info btn-sm"
                            href="{{route('evidence.edit',['instance' => $instance, 'id' => $task->id])}}">
                            <i class="fas fa-pencil-alt">
                            </i>
                        </a>

                        <x-buttonconfirm :id="$task->id" route="evidence.remove" title="¿Seguro?" description="Esto borrará la tarea actual de forma <b>permanente.</b>" type="REMOVE"/>
-->
                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4>Estado</h4>
                    <!--
                    Añadir
                    -->
                </div>

            </div>

        </div>

    </div>


@endsection
