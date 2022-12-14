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
                    <h5><b>Fecha Inicio:</b> {{$task->start_date}}</h5>
                    <h5><b>Fecha Inicio:</b> {{$task->end_date}}</h5>
                    <h5><b>Descripción:</b></h5>  {!! $task->description !!}
                    <br></br>

                        <a class="btn btn-info btn-sm"
                            href="{{route('task.edit',['instance' => $instance, 'id' => $task->id])}}">
                            <i class="fas fa-pencil-alt">
                            </i>
                        </a>
                        <x-buttonconfirm :id="$task->id" route="task.remove" title="¿Seguro?" description="Esto borrará la tarea actual de forma <b>permanente.</b>" type="REMOVE"/>

                </div>

            </div>

        </div>

    </div>


@endsection
